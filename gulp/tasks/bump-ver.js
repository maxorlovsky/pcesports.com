const gulp = require('gulp');
const fs = require('fs');

const packageJson = require('../../package.json');

gulp.task("bump-ver", () => {
    const indexFilePath = './public/index.html';
	let indexFileTemp = fs.readFileSync(indexFilePath, 'utf-8');
    let indexFile = indexFileTemp.replace(/%version%/g, packageJson.version);
    fs.writeFileSync(indexFilePath, indexFile);
});