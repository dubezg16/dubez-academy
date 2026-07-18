const fs = require('fs');
let c = fs.readFileSync('generate_report.js', 'utf8');

// Fix 1: standalone `new PageBreak()` used as array element -> wrap in Paragraph
c = c.replace(/new PageBreak\(\)/g, 'new Paragraph({children:[new PageBreak()]})');

// Fix 2: make sure PageBreak is imported (it already is, but verify)
if (!c.includes('PageBreak')) {
    console.error('PageBreak not imported!');
    process.exit(1);
}

fs.writeFileSync('generate_report.js', c);
console.log('Fix applied successfully.');
