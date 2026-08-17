import fs from 'node:fs';
import path from 'node:path';

const [sourceJson, outputDir] = process.argv.slice(2);
if (!sourceJson || !outputDir) throw new Error('Usage: node build_grade6_english_cache.mjs <pages.json> <output-dir>');

const payload = JSON.parse(fs.readFileSync(sourceJson, 'utf8'));
const lessons = [
  [1, 'Hello'],
  [2, 'Leisure'],
  [3, "Where's Everything?"],
  [4, 'What Can You See?'],
  [5, "Aunt Minoli's Kitchen"],
  [6, 'What We Do'],
  [7, 'A Fine Day'],
  [8, 'A Visit to the Zoo'],
  [9, 'Sugar or Sand'],
  [10, 'Was It Yesterday?'],
  [11, 'Eco Friends'],
];

const starts = [];
for (const [number] of lessons) {
  const pattern = new RegExp(`\\bUNIT\\s*0?${number}\\b`, 'i');
  const index = payload.pages.findIndex((page) => pattern.test(page.text));
  if (index < 0) throw new Error(`Could not find Unit ${number}.`);
  starts.push(index);
}

fs.mkdirSync(outputDir, { recursive: true });
for (let i = 0; i < lessons.length; i += 1) {
  const [number, title] = lessons[i];
  const pages = payload.pages.slice(starts[i], starts[i + 1] ?? payload.pages.length);
  const chunks = pages
    .map((page, index) => ({ page: page.page, index, text: page.text.replace(/For free distribution/gi, '').trim() }))
    .filter((chunk) => chunk.text.length > 20);
  fs.writeFileSync(path.join(outputDir, `lesson-${number}.json`), JSON.stringify({
    title,
    subject: 'English',
    grade: 6,
    language: 'en',
    source: 'english PB G-6.pdf',
    chunks,
  }, null, 2));
}

fs.writeFileSync(path.join(outputDir, 'catalog.json'), JSON.stringify({
  english: { subject: 'English', lessons: lessons.map(([number, title]) => ({ number, title })) },
}, null, 2));
console.log(`Built ${lessons.length} Grade 6 English lesson files.`);
