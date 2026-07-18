const fs = require('fs');
const { Document, Packer, Paragraph, TextRun, HeadingLevel } = require("docx");

const doc = new Document({
    sections: [
        {
            properties: {},
            children: [
                new Paragraph({
                    text: "Dubez Academy Test Document",
                    heading: HeadingLevel.HEADING_1
                }),
                new Paragraph({
                    children: [
                        new TextRun("This is a simple test run to verify the docx package works."),
                    ],
                }),
            ]
        }
    ]
});

Packer.toBuffer(doc).then((buffer) => {
    fs.writeFileSync("test.docx", buffer);
    console.log("Successfully generated test.docx");
}).catch((err) => {
    console.error("Error generating docx:", err);
});
