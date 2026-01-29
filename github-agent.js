const GitHubAgent = require('./src/GitHubAgent');
const config = require('./config/agentConfig');

async function main() {
  const agent = new GitHubAgent(config);
  
  const command = process.argv[2];
  const args = process.argv.slice(3);
  
  try {
    switch (command) {
      case 'commit':
        await agent.smartCommit(args[0] || 'auto');
        break;
      case 'comment':
        await agent.manageComments(args[0], args[1]);
        break;
      case 'version':
        await agent.manageVersion(args[0]);
        break;
      case 'status':
        await agent.getStatus();
        break;
      default:
        console.log('Available commands: commit, comment, version, status');
        process.exit(1);
    }
  } catch (error) {
    console.error('Error:', error.message);
    process.exit(1);
  }
}

if (require.main === module) {
  main();
}

module.exports = { main };