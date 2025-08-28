const { execSync } = require('child_process');
const fs = require('fs-extra');
const path = require('path');

console.log('🔨 Building Nolads Engineering frontend...');

try {
  // Change to client directory and build
  process.chdir(path.join(__dirname, 'client'));
  console.log('📦 Installing dependencies...');
  execSync('npm install', { stdio: 'inherit' });
  
  console.log('⚛️  Building React application...');
  execSync('npm run build', { stdio: 'inherit' });
  
  // Go back to root directory
  process.chdir(__dirname);
  
  console.log('📁 Copying build files to root...');
  
  // Copy built files to root directory
  const distPath = path.join(__dirname, 'client', 'dist');
  
  // Copy index.html
  if (fs.existsSync(path.join(distPath, 'index.html'))) {
    fs.copySync(path.join(distPath, 'index.html'), path.join(__dirname, 'index.html'));
    console.log('✅ Copied index.html');
  }
  
  // Copy assets directory
  if (fs.existsSync(path.join(distPath, 'assets'))) {
    fs.removeSync(path.join(__dirname, 'assets')); // Remove existing assets
    fs.copySync(path.join(distPath, 'assets'), path.join(__dirname, 'assets'));
    console.log('✅ Copied assets directory');
  }
  
  // Copy any other static files
  const staticFiles = ['manifest.json', 'robots.txt', 'sitemap.xml'];
  staticFiles.forEach(file => {
    if (fs.existsSync(path.join(distPath, file))) {
      fs.copySync(path.join(distPath, file), path.join(__dirname, file));
      console.log(`✅ Copied ${file}`);
    }
  });
  
  console.log('🎉 Build completed successfully!');
  console.log('🚀 You can now run: npm start');
  
} catch (error) {
  console.error('❌ Build failed:', error.message);
  process.exit(1);
}
