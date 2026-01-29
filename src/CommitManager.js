const { execSync } = require('child_process');

class CommitManager {
  constructor(config) {
    this.config = config.commits;
    this.githubConfig = config.github;
  }

  async smartCommit(mode = 'auto') {
    if (mode === 'auto') {
      await this.autoCommit();
    } else {
      await this.manualCommit(mode);
    }
  }

  async autoCommit() {
    console.log('📝 Analyzing changes for automatic commit...');
    
    const status = execSync('git status --porcelain', { encoding: 'utf8' });
    if (!status.trim()) {
      console.log('✅ No changes to commit.');
      return;
    }

    if (this.config.autoAdd) {
      console.log('➕ Adding all changes...');
      execSync('git add .');
    }

    const message = this.generateCommitMessage();
    console.log(`💬 Generated commit message: "${message}"`);

    execSync(`git commit -m "${message}"`);
    console.log('✅ Commit successful');

    if (this.config.autoPush) {
      try {
        console.log('🚀 Pushing changes...');
        execSync(`git push origin ${this.githubConfig.defaultBranch}`);
        console.log('✅ Push successful');
      } catch (error) {
        console.error('⚠️  Failed to push changes:', error.message);
      }
    }
  }

  generateCommitMessage() {
    const status = execSync('git status --porcelain', { encoding: 'utf8' });
    const files = status.split('\n').filter(line => line.trim());
    
    if (files.length === 0) return 'chore: minor updates';

    const firstFile = files[0].substring(3);
    const type = this.inferCommitType(firstFile);
    const scope = this.inferScope(firstFile);
    
    let message = `${type}${scope ? `(${scope})` : ''}: updates to ${files.length} file(s)`;
    
    if (this.config.conventionalCommits) {
      if (files.some(f => f.includes('test'))) message = `test: add/update tests`;
      else if (files.some(f => f.includes('docs')) || files.some(f => f.endsWith('.md'))) message = `docs: update documentation`;
      else if (files.some(f => f.includes('src/Models'))) message = `feat: update domain models`;
      else if (files.some(f => f.includes('src/Controllers'))) message = `feat: update api controllers`;
    }

    return message.substring(0, this.config.maxCommitMessageLength);
  }

  inferCommitType(filename) {
    if (filename.includes('test')) return 'test';
    if (filename.includes('docs') || filename.endsWith('.md')) return 'docs';
    if (filename.includes('src/')) return 'feat';
    if (filename.includes('config/')) return 'chore';
    return 'fix';
  }

  inferScope(filename) {
    const parts = filename.split('/');
    if (parts.length > 1) {
      const scope = parts[parts.length - 2].toLowerCase();
      return scope === 'src' ? '' : scope;
    }
    return '';
  }

  async manualCommit(message) {
    if (this.config.autoAdd) {
      execSync('git add .');
    }
    execSync(`git commit -m "${message}"`);
    console.log('✅ Commit successful');
  }
}

module.exports = CommitManager;