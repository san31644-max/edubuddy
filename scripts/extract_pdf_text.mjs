import fs from 'node:fs';
import { pathToFileURL } from 'node:url';

const [pdfPath, outputPath, pdfJsPath] = process.argv.slice(2);
if (!pdfPath || !outputPath || !pdfJsPath) {
  throw new Error('Usage: node extract_pdf_text.mjs <pdf> <output.json> <pdf.mjs>');
}

const { getDocument } = await import(pathToFileURL(pdfJsPath).href);
const data = new Uint8Array(fs.readFileSync(pdfPath));
const document = await getDocument({ data, useWorkerFetch: false, isEvalSupported: false }).promise;
const pages = [];
for (let pageNumber = 1; pageNumber <= document.numPages; pageNumber += 1) {
  const page = await document.getPage(pageNumber);
  const content = await page.getTextContent();
  const lines = [];
  let currentY = null;
  let current = [];
  for (const item of content.items) {
    const y = Math.round(item.transform?.[5] ?? 0);
    if (currentY !== null && Math.abs(y - currentY) > 2) {
      lines.push(current.join(' ').replace(/\s+/g, ' ').trim());
      current = [];
    }
    currentY = y;
    if (item.str) current.push(item.str);
  }
  if (current.length) lines.push(current.join(' ').replace(/\s+/g, ' ').trim());
  pages.push({ page: pageNumber, text: lines.filter(Boolean).join('\n') });
}
fs.writeFileSync(outputPath, JSON.stringify({ page_count: document.numPages, pages }, null, 2));
console.log(`Extracted ${document.numPages} pages.`);
