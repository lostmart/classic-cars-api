module.exports = {
  github: {
    token: process.env.GITHUB_TOKEN || '',
    defaultBranch: 'main',
    repoOwner: process.env.GITHUB_USER || '',
    repoName: process.env.GITHUB_REPO || ''
  },
  commits: {
    autoAdd: true,
    conventionalCommits: true,
    maxCommitMessageLength: 72,
    autoPush: false,
    requireTests: false
  },
  comments: {
    autoReply: true,
    templates: {
      mergeRequest: 'Thank you for your contribution! 🚀',
      issue: 'We are looking into this issue. 👍',
      bugReport: 'Bug acknowledged. Will prioritize this fix. 🐛'
    },
    keywords: ['bug', 'feature', 'help wanted', 'documentation']
  },
  versioning: {
    strategy: 'semantic', // semantic, calendar, custom
    autoTag: true,
    autoRelease: false,
    releaseNotes: true,
    bumpRules: {
      major: ['BREAKING CHANGE:'],
      minor: ['feat:', 'feature:'],
      patch: ['fix:', 'docs:', 'style:', 'refactor:', 'test:', 'chore:']
    }
  },
  notifications: {
    slack: process.env.SLACK_WEBHOOK_URL || '',
    discord: process.env.DISCORD_WEBHOOK_URL || '',
    email: process.env.NOTIFICATION_EMAIL || ''
  }
};