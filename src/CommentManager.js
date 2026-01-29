const { Octokit } = require('@octokit/core');
const fs = require('fs');
const path = require('path');

class CommentManager {
  constructor(config) {
    this.config = config.comments;
    this.octokit = new Octokit({ auth: config.github.token });
    this.repoOwner = config.github.repoOwner;
    this.repoName = config.github.repoName;
  }

  async manageComments(action, target) {
    switch (action) {
      case 'auto-reply':
        await this.setupAutoReply();
        break;
      case 'list':
        await this.listComments(target);
        break;
      case 'create':
        await this.createComment(target, process.argv[4] || 'Automated comment');
        break;
      case 'delete':
        await this.deleteComment(target);
        break;
      default:
        console.log('Available actions: auto-reply, list, create, delete');
    }
  }

  async setupAutoReply() {
    if (!this.config.autoReply) {
      console.log('Auto-reply is disabled');
      return;
    }

    console.log('Setting up auto-reply system...');
    
    const issues = await this.getRecentIssues();
    const prs = await this.getRecentPRs();

    for (const issue of issues) {
      await this.handleIssueComments(issue);
    }

    for (const pr of prs) {
      await this.handlePRComments(pr);
    }
  }

  async getRecentIssues() {
    try {
      const response = await this.octokit.request('GET /repos/{owner}/{repo}/issues', {
        owner: this.repoOwner,
        repo: this.repoName,
        state: 'open',
        sort: 'updated',
        direction: 'desc',
        per_page: 10
      });
      return response.data.filter(issue => !issue.pull_request);
    } catch (error) {
      console.error('Failed to fetch issues:', error.message);
      return [];
    }
  }

  async getRecentPRs() {
    try {
      const response = await this.octokit.request('GET /repos/{owner}/{repo}/pulls', {
        owner: this.repoOwner,
        repo: this.repoName,
        state: 'open',
        sort: 'updated',
        direction: 'desc',
        per_page: 10
      });
      return response.data;
    } catch (error) {
      console.error('Failed to fetch PRs:', error.message);
      return [];
    }
  }

  async handleIssueComments(issue) {
    const comments = await this.getComments(issue.number);
    const hasBotReply = comments.some(comment => 
      comment.user.type === 'Bot' && comment.body.includes('automated')
    );

    if (!hasBotReply) {
      const comment = this.generateIssueComment(issue);
      await this.createComment(issue.number, comment);
    }
  }

  async handlePRComments(pr) {
    const comments = await this.getPRComments(pr.number);
    const hasBotReply = comments.some(comment => 
      comment.user.type === 'Bot' && comment.body.includes('automated')
    );

    if (!hasBotReply) {
      const comment = this.generatePRComment(pr);
      await this.createPRComment(pr.number, comment);
    }
  }

  async getComments(issueNumber) {
    try {
      const response = await this.octokit.request('GET /repos/{owner}/{repo}/issues/{issue_number}/comments', {
        owner: this.repoOwner,
        repo: this.repoName,
        issue_number: issueNumber
      });
      return response.data;
    } catch (error) {
      console.error('Failed to fetch comments:', error.message);
      return [];
    }
  }

  async getPRComments(prNumber) {
    try {
      const response = await this.octokit.request('GET /repos/{owner}/{repo}/pulls/{pull_number}/comments', {
        owner: this.repoOwner,
        repo: this.repoName,
        pull_number: prNumber
      });
      return response.data;
    } catch (error) {
      console.error('Failed to fetch PR comments:', error.message);
      return [];
    }
  }

  generateIssueComment(issue) {
    const labels = issue.labels.map(label => label.name.toLowerCase());
    
    for (const keyword of this.config.keywords) {
      if (labels.includes(keyword)) {
        return this.getTemplateForKeyword(keyword, issue);
      }
    }

    if (labels.includes('bug')) {
      return this.config.templates.bugReport;
    }

    return this.config.templates.issue;
  }

  generatePRComment(pr) {
    if (pr.state === 'open') {
      return this.config.templates.mergeRequest;
    }
    return '';
  }

  getTemplateForKeyword(keyword, issue) {
    const templates = {
      'bug': this.config.templates.bugReport,
      'feature': `Feature request acknowledged! 🎯 We'll consider this for future releases.`,
      'help wanted': `Community help appreciated! 🙏 Anyone interested in contributing to this issue?`,
      'documentation': `Documentation improvement noted! 📚 We'll update our docs accordingly.`
    };

    return templates[keyword] || this.config.templates.issue;
  }

  async listComments(target) {
    const targetType = target.includes('pr') ? 'pull' : 'issue';
    const targetNumber = this.extractNumber(target);

    if (!targetNumber) {
      console.error('Invalid target format. Use: issue#123 or pr#456');
      return;
    }

    const comments = targetType === 'pull' ? 
      await this.getPRComments(targetNumber) : 
      await this.getComments(targetNumber);

    console.log(`Comments for ${target}:`);
    comments.forEach((comment, index) => {
      console.log(`${index + 1}. @${comment.user.login}: ${comment.body.substring(0, 100)}...`);
    });
  }

  async createComment(target, message) {
    const targetType = target.includes('pr') ? 'pulls' : 'issues';
    const targetNumber = this.extractNumber(target);

    if (!targetNumber) {
      console.error('Invalid target format. Use: issue#123 or pr#456');
      return;
    }

    try {
      const response = await this.octokit.request('POST /repos/{owner}/{repo}/{target_type}/{target_number}/comments', {
        owner: this.repoOwner,
        repo: this.repoName,
        target_type: targetType,
        target_number: targetNumber,
        body: message
      });

      console.log(`✅ Comment created on ${target}`);
      return response.data;
    } catch (error) {
      console.error('Failed to create comment:', error.message);
    }
  }

  async createPRComment(prNumber, message) {
    try {
      const response = await this.octokit.request('POST /repos/{owner}/{repo}/pulls/{pull_number}/comments', {
        owner: this.repoOwner,
        repo: this.repoName,
        pull_number: prNumber,
        body: message
      });

      console.log(`✅ Comment created on PR #${prNumber}`);
      return response.data;
    } catch (error) {
      console.error('Failed to create PR comment:', error.message);
    }
  }

  async deleteComment(target) {
    const commentId = this.extractNumber(target);

    if (!commentId) {
      console.error('Invalid comment ID. Use: comment#789');
      return;
    }

    try {
      await this.octokit.request('DELETE /repos/{owner}/{repo}/issues/comments/{comment_id}', {
        owner: this.repoOwner,
        repo: this.repoName,
        comment_id: commentId
      });

      console.log(`✅ Comment #${commentId} deleted`);
    } catch (error) {
      console.error('Failed to delete comment:', error.message);
    }
  }

  extractNumber(target) {
    const match = target.match(/#(\d+)/);
    return match ? parseInt(match[1]) : null;
  }

  saveCommentTemplate(type, template) {
    this.config.templates[type] = template;
    this.saveConfig();
  }

  saveConfig() {
    const configPath = path.join(__dirname, '../config/agentConfig.js');
    const configContent = `module.exports = ${JSON.stringify(this.config, null, 2)};`;
    fs.writeFileSync(configPath, configContent);
  }
}

module.exports = CommentManager;