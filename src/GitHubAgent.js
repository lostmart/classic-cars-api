const CommitManager = require('./CommitManager');
const CommentManager = require('./CommentManager');
const VersionManager = require('./VersionManager');
const { execSync } = require('child_process');

class GitHubAgent {
  constructor(config) {
    this.config = config;
    this.commitManager = new CommitManager(config);
    this.commentManager = new CommentManager(config);
    this.versionManager = new VersionManager(config);
  }

  async smartCommit(mode = 'auto') {
    console.log('🚀 Starting smart commit process...');
    
    try {
      await this.commitManager.smartCommit(mode);
      
      const needsRelease = await this.versionManager.isReleaseNeeded();
      if (needsRelease && this.config.versioning.autoTag) {
        console.log('📦 New release detected, preparing version management...');
        const newVersion = await this.versionManager.bumpVersion();
        console.log(`🎉 Version bumped to ${newVersion}`);
      }
      
    } catch (error) {
      console.error('❌ Smart commit failed:', error.message);
      throw error;
    }
  }

  async manageComments(action, target) {
    console.log(`💬 Managing comments: ${action} ${target || ''}`);
    
    try {
      await this.commentManager.manageComments(action, target);
    } catch (error) {
      console.error('❌ Comment management failed:', error.message);
      throw error;
    }
  }

  async manageVersion(action) {
    console.log(`📋 Managing version: ${action}`);
    
    try {
      await this.versionManager.manageVersion(action);
    } catch (error) {
      console.error('❌ Version management failed:', error.message);
      throw error;
    }
  }

  async getStatus() {
    console.log('📊 Repository Status Report');
    console.log('============================');
    
    try {
      const gitStatus = execSync('git status --porcelain', { encoding: 'utf8' });
      const currentBranch = execSync('git rev-parse --abbrev-ref HEAD', { encoding: 'utf8' }).trim();
      const currentVersion = this.versionManager.getCurrentVersion();
      const latestTag = this.versionManager.getLatestTag();
      
      console.log(`Branch: ${currentBranch}`);
      console.log(`Current Version: ${currentVersion}`);
      console.log(`Latest Tag: ${latestTag || 'None'}`);
      
      if (gitStatus.trim()) {
        console.log('\n📝 Modified Files:');
        gitStatus.split('\n').filter(line => line.trim()).forEach(line => {
          const status = line[0];
          const file = line.substring(3);
          const icon = status === 'M' ? '📝' : status === 'A' ? '➕' : status === 'D' ? '🗑️' : '❓';
          console.log(`${icon} ${file}`);
        });
      } else {
        console.log('\n✅ Working directory clean');
      }

      const commitHistory = execSync('git log --oneline -5', { encoding: 'utf8' });
      console.log('\n📜 Recent Commits:');
      commitHistory.split('\n').forEach(line => {
        console.log(`  ${line}`);
      });

    } catch (error) {
      console.error('❌ Failed to get status:', error.message);
    }
  }

  async workflow(preset = 'standard') {
    console.log(`🔄 Running ${preset} workflow...`);
    
    switch (preset) {
      case 'commit':
        await this.smartCommit('auto');
        break;
      case 'release':
        await this.smartCommit('auto');
        await this.versionManager.createRelease();
        break;
      case 'maintenance':
        await this.manageComments('auto-reply');
        await this.manageVersion('changelog');
        break;
      case 'standard':
      default:
        await this.smartCommit('auto');
        if (await this.versionManager.isReleaseNeeded()) {
          console.log('📦 Release recommended');
        }
        break;
    }
  }

  async setup() {
    console.log('🔧 Setting up GitHub Agent...');
    
    if (!this.config.github.token) {
      console.log('⚠️  GitHub token not configured. Please set GITHUB_TOKEN environment variable.');
    }
    
    if (!this.config.github.repoOwner || !this.config.github.repoName) {
      console.log('⚠️  Repository owner/name not configured.');
      const remote = execSync('git remote get-url origin', { encoding: 'utf8' }).trim();
      const match = remote.match(/github\.com[:/]([^/]+)\/(.+)\.git/);
      if (match) {
        this.config.github.repoOwner = match[1];
        this.config.github.repoName = match[2];
        console.log(`✅ Auto-detected repository: ${match[1]}/${match[2]}`);
      }
    }
    
    console.log('✅ GitHub Agent setup complete');
  }

  configure(key, value) {
    const keys = key.split('.');
    let current = this.config;
    
    for (let i = 0; i < keys.length - 1; i++) {
      if (!current[keys[i]]) {
        current[keys[i]] = {};
      }
      current = current[keys[i]];
    }
    
    current[keys[keys.length - 1]] = value;
    console.log(`✅ Configuration updated: ${key} = ${value}`);
  }

  getConfig(key = null) {
    if (key) {
      const keys = key.split('.');
      let current = this.config;
      for (const k of keys) {
        current = current[k];
        if (current === undefined) return undefined;
      }
      return current;
    }
    return this.config;
  }

  async validate() {
    console.log('🔍 Validating GitHub Agent configuration...');
    
    const issues = [];
    
    if (!this.config.github.token) {
      issues.push('GitHub token is missing');
    }
    
    if (!this.config.github.repoOwner || !this.config.github.repoName) {
      issues.push('Repository owner/name is missing');
    }
    
    try {
      execSync('git --version', { encoding: 'utf8' });
    } catch (error) {
      issues.push('Git is not available');
    }
    
    if (issues.length > 0) {
      console.log('❌ Validation failed:');
      issues.forEach(issue => console.log(`  - ${issue}`));
      return false;
    }
    
    console.log('✅ Validation successful');
    return true;
  }
}

module.exports = GitHubAgent;