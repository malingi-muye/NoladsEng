const { spawn } = require('child_process');
const path = require('path');

// Function to spawn a process and handle output
function spawnProcess(command, args, cwd, name) {
  const process = spawn(command, args, { 
    cwd, 
    stdio: 'inherit', 
    shell: true 
  });

  process.on('error', (error) => {
    console.error(`${name} error:`, error);
  });

  process.on('close', (code) => {
    console.log(`${name} exited with code ${code}`);
  });

  return process;
}

console.log('🚀 Starting development environment...');

// Start the Node.js backend server
console.log('📱 Starting backend server...');
const backendProcess = spawnProcess('node', ['server.js'], __dirname, 'Backend');

// Start the Vite development server for the frontend
console.log('⚛️  Starting frontend development server...');
const frontendProcess = spawnProcess('npm', ['run', 'dev'], path.join(__dirname, 'client'), 'Frontend');

// Handle process termination
process.on('SIGINT', () => {
  console.log('\n🛑 Shutting down development environment...');
  
  if (backendProcess) {
    backendProcess.kill('SIGINT');
  }
  
  if (frontendProcess) {
    frontendProcess.kill('SIGINT');
  }
  
  process.exit(0);
});

process.on('SIGTERM', () => {
  console.log('\n🛑 Shutting down development environment...');
  
  if (backendProcess) {
    backendProcess.kill('SIGTERM');
  }
  
  if (frontendProcess) {
    frontendProcess.kill('SIGTERM');
  }
  
  process.exit(0);
});

console.log('✅ Development environment started!');
console.log('📱 Backend: http://localhost:3000');
console.log('⚛️  Frontend: http://localhost:5173');
console.log('\nPress Ctrl+C to stop both servers');
