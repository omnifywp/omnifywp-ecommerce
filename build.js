const fs = require('fs');
const path = require('path');
const os = require('os');
const { execSync } = require('child_process');

const pluginSlug = 'omnifywp-ecommerce';
const buildDir = path.join(__dirname, 'dist');
const tempBuildRoot = fs.mkdtempSync(path.join(os.tmpdir(), `${pluginSlug}-build-`));
const tempPluginDir = path.join(tempBuildRoot, pluginSlug);
const zipFile = path.join(buildDir, `${pluginSlug}.zip`);

// Files and folders to copy to the shareable build
const includeItems = [
    'assets',
    'includes',
    'templates',
    'languages',
    'index.php',
    'omnifywp-ecommerce.php',
    'readme.txt',
    'blueprint.json'
];

console.log('[Omnify] Starting build process...');

function cleanupTempBuild() {
    if (fs.existsSync(tempBuildRoot)) {
        fs.rmSync(tempBuildRoot, { recursive: true, force: true });
    }
}

// 1. Clean build directory
if (fs.existsSync(buildDir)) {
    console.log('[Omnify] Cleaning old build directory...');
    fs.rmSync(buildDir, { recursive: true, force: true });
}
fs.mkdirSync(buildDir, { recursive: true });

// 2. Create directories
fs.mkdirSync(tempPluginDir, { recursive: true });

// Exclude unwanted files, OS clutter, and unneeded assets from plugin builds
const excludeFilePatterns = [
    /^\.DS_Store$/i,
    /^Thumbs\.db$/i,
    /\.map$/i,
    /\.tmp$/i,
    /\.log$/i,
    /^banner-.*\.(png|jpg|jpeg|webp)$/i,
    /^shop-banner\.(png|jpg|jpeg|webp)$/i,
    /^catalog-hero\.(png|jpg|jpeg|webp)$/i,
    /^product-.*\.(png|jpg|jpeg|webp)$/i,
    /^demo-.*\.(png|jpg|jpeg|webp)$/i,
    /^dummy-.*\.(png|jpg|jpeg|webp)$/i,
    /\.gif$/i
];

// 3. Helper to recursively copy directories
function copyRecursiveSync(src, dest) {
    const exists = fs.existsSync(src);
    const stats = exists && fs.statSync(src);
    const isDirectory = exists && stats.isDirectory();
    if (isDirectory) {
        fs.mkdirSync(dest, { recursive: true });
        fs.readdirSync(src).forEach((childItemName) => {
            if (excludeFilePatterns.some((pattern) => pattern.test(childItemName))) {
                return;
            }
            copyRecursiveSync(
                path.join(src, childItemName),
                path.join(dest, childItemName)
            );
        });
    } else {
        const baseName = path.basename(src);
        if (excludeFilePatterns.some((pattern) => pattern.test(baseName))) {
            return;
        }
        fs.copyFileSync(src, dest);
    }
}

// 4. Copy files
console.log('[Omnify] Copying plugin files...');
includeItems.forEach((item) => {
    const srcPath = path.join(__dirname, item);
    const destPath = path.join(tempPluginDir, item);
    if (fs.existsSync(srcPath)) {
        copyRecursiveSync(srcPath, destPath);
    } else {
        console.warn(`[Warning] ${item} does not exist!`);
    }
});

// 5. Create ZIP archive
console.log('[Omnify] Zipping package...');
try {
    if (fs.existsSync(zipFile)) {
        fs.rmSync(zipFile, { force: true });
    }

    if (process.platform === 'win32') {
        try {
            execSync(`tar -a -c -f "${zipFile}" -C "${tempBuildRoot}" "${pluginSlug}"`, { stdio: 'inherit' });
        } catch {
            const sourcePath = tempPluginDir;
            const psSourcePath = sourcePath.replace(/'/g, "''");
            const psZipFile = zipFile.replace(/'/g, "''");
            execSync(
                `powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference = 'Stop'; Compress-Archive -Path '${psSourcePath}' -DestinationPath '${psZipFile}' -Force"`,
                { stdio: 'inherit' }
            );
        }
    } else {
        execSync(`cd "${tempBuildRoot}" && zip -r "${zipFile}" "${pluginSlug}"`, { stdio: 'inherit' });
    }
    console.log(`[Success] Build successful! Shareable plugin ready at: ${zipFile}`);
} catch (error) {
    console.error('[Error] Error creating ZIP archive:', error);
    cleanupTempBuild();
    process.exit(1);
}

// 6. Clean up temporary directory
console.log('[Omnify] Cleaning up temporary files...');
cleanupTempBuild();
console.log('[Omnify] Build process complete!');
