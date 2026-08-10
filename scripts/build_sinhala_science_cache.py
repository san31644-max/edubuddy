"""Build Unicode Sinhala lesson caches from the legacy-font Grade 6 PDF.

Dependencies: py -m pip install pypdf pandukabhaya
"""

import json
from pathlib import Path

from pandukabhaya import Converter
from pypdf import PdfReader


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "uploads" / "syllabus" / "science g-6 Sinhala.pdf"
TARGET = ROOT / "uploads" / "syllabus" / "science-cache" / "si"

# Printed textbook page ranges from the book's table of contents.
LESSONS = {
    1: ("ජෛව ලෝකයේ අසිරිය", 1, 20),
    2: ("අප අවට ඇති දේ", 21, 33),
    3: ("ජලය ස්වාභාවික සම්පතක් ලෙස", 34, 47),
    4: ("එදිනෙදා ජීවිතයේදී ශක්තිය", 48, 65),
    5: ("ආලෝකය හා පෙනීම", 66, 84),
    6: ("ශබ්දය හා ඇසීම", 85, 96),
    7: ("චුම්බක", 97, 107),
    8: ("සුවපහසු දිවියක් සඳහා විදුලිය", 108, 130),
    9: ("තාපය හා එහි බලපෑම්", 131, 145),
    10: ("ආහාර හා බැඳුණු අන්තර්ක්‍රියා", 146, 156),
    11: ("කාලගුණය හා දේශගුණය", 157, 172),
}


def main() -> None:
    reader = PdfReader(SOURCE)
    converter = Converter()
    target = TARGET
    target.mkdir(parents=True, exist_ok=True)

    # Printed page 1 is PDF page 13 (zero-based index 12).
    pdf_offset = 12
    for number, (title, first_page, last_page) in LESSONS.items():
        chunks = []
        for printed_page in range(first_page, last_page + 1):
            raw = reader.pages[pdf_offset + printed_page - 1].extract_text() or ""
            text = converter.convert(raw).strip()
            if text:
                chunks.append({"page": printed_page, "index": 0, "text": text})
        payload = {
            "title": f"{number}. {title}",
            "language": "si",
            "source": SOURCE.name,
            "chunks": chunks,
        }
        output = target / f"lesson-{number}.json"
        output.write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")
        print(f"{output.name}: {len(chunks)} pages")


if __name__ == "__main__":
    main()
