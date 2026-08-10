"""Build Grade 6 textbook lesson caches and the chatbot catalog.

Dependency: py -m pip install pypdf
"""

import json
import re
from pathlib import Path

from pypdf import PdfReader


ROOT = Path(__file__).resolve().parents[1]
BOOKS = ROOT / "uploads" / "syllabus" / "GRADE6 ENGLISH MEDIUM"
TARGET = ROOT / "uploads" / "syllabus" / "textbook-cache"


SOURCES = [
    ("english", "English", "english PB G-6.pdf", [
        (1, "Hello", 15), (2, "Leisure", 25), (3, "Where's Everything?", 36),
        (4, "What Can You See?", 46), (5, "Aunt Minoli's Kitchen", 53),
        (6, "What We Do", 63), (7, "A Fine Day", 77),
        (8, "A Visit to the Zoo", 85), (9, "Sugar or Sand", 98),
        (10, "Was It Yesterday?", 114), (11, "Eco Friends", 125),
    ]),
    ("geography", "Geography", "geo G-6 E.pdf", [
        (1, "The School and Its Immediate Surroundings", 13),
        (2, "Nature of the Land in the Immediate Surroundings of the House", 34),
        (3, "Good Maintenance of the Immediate Surroundings of Your Home", 50),
        (4, "Location of Sri Lanka", 68),
    ]),
    ("health", "Health and Physical Education", "health G-6 E.pdf", [
        (1, "Let Us Lead a Happy and Healthy Life", 13),
        (2, "Let Us Identify Needs and Desires", 52),
        (3, "Let Us Improve Personality Through Posture", 60),
        (4, "Let Us Enjoy Our Leisure Through Recreational Games", 74),
        (5, "Let Us Develop Basic Athletic Skills", 91),
        (6, "Let Us Respect Rules, Regulations and Ethics in Sports", 102),
        (7, "Let Us Get Used to Healthy Food Habits", 109),
        (8, "Let Us Maintain a Healthy Body", 123),
        (9, "Let Us Improve Fitness for a Balanced Life", 134),
        (10, "Let Us Be Aware and Face Challenges", 148),
    ]),
    ("history", "History", "History G6 (E).pdf", [
        (1, "Defining History", 9), (2, "Ancient Man", 15),
        (3, "Ancient Civilizations in the World", 29),
        (4, "Settlements in Sri Lanka", 39), (5, "Our Brave Kings", 45),
    ]),
    ("ict", "ICT", "ICT G-6 E PB.pdf", [
        (1, "Importance of Computers", 10),
        (2, "Use the Computer Laboratory Safely", 23),
        (3, "Operating System and File Management", 36),
        (4, "Using Mouse and Keyboard to Use Application Software", 50),
        (5, "Algorithm and Flow Charts", 64),
        (6, "Using the Internet for Information and Communication", 73),
    ]),
    ("mathematics", "Mathematics", "maths G-6 E P-I.pdf", [
        (1, "Circles", 11), (2, "Place Value", 18),
        (3, "Mathematical Operations on Whole Numbers", 32), (4, "Time", 49),
        (5, "Number Line", 72), (6, "Estimation and Rounding Off", 86),
        (7, "Angles", 95), (8, "Directions", 106), (9, "Fractions", 122),
        (10, "Selection", 145), (11, "Factors and Multiples", 150),
    ]),
    ("mathematics", "Mathematics", "maths G-6 P-II E.pdf", [
        (12, "Rectilinear Plane Figures", 13), (13, "Decimals", 22),
        (14, "Types of Numbers and Number Patterns", 38), (15, "Length", 52),
        (16, "Liquid Measurements", 73), (17, "Solids", 85),
        (18, "Algebraic Symbols", 102),
        (19, "Constructing Algebraic Expressions and Substitution", 107),
        (20, "Mass", 113), (21, "Ratio", 126),
        (22, "Data Collection and Representation", 138),
        (23, "Data Interpretation", 150), (24, "Indices", 159),
        (25, "Area", 165),
    ]),
]


def clean(text: str) -> str:
    text = text.replace("\u00ad", "")
    text = re.sub(r"[ \t]+", " ", text)
    text = re.sub(r"\n{3,}", "\n\n", text)
    return text.strip()


def write_book(slug: str, subject: str, filename: str, lessons: list[tuple]) -> list[dict]:
    source = BOOKS / filename
    reader = PdfReader(source)
    output_dir = TARGET / "en" / slug
    output_dir.mkdir(parents=True, exist_ok=True)
    catalog = []
    for position, (number, title, first_pdf_page) in enumerate(lessons):
        last_pdf_page = lessons[position + 1][2] - 1 if position + 1 < len(lessons) else len(reader.pages)
        chunks = []
        for pdf_page in range(first_pdf_page, last_pdf_page + 1):
            text = clean(reader.pages[pdf_page - 1].extract_text() or "")
            if text:
                chunks.append({"page": pdf_page, "index": 0, "text": text})
        payload = {
            "title": f"Lesson {number}: {title}", "subject": subject,
            "language": "en", "source": filename, "chunks": chunks,
        }
        (output_dir / f"lesson-{number}.json").write_text(
            json.dumps(payload, ensure_ascii=False), encoding="utf-8"
        )
        catalog.append({"number": number, "title": title})
        print(f"{subject} {number}: {len(chunks)} PDF pages")
    return catalog


def import_existing_science(catalog: list[dict]) -> None:
    english_target = TARGET / "en" / "science"
    english_target.mkdir(parents=True, exist_ok=True)
    old = ROOT / "uploads" / "syllabus" / "science-cache"
    for lesson in catalog:
        source = old / f"lesson-{lesson['number']}.json"
        payload = json.loads(source.read_text(encoding="utf-8"))
        payload.update(subject="Science", language="en")
        (english_target / source.name).write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")

    sinhala_target = TARGET / "si" / "science"
    sinhala_target.mkdir(parents=True, exist_ok=True)
    for source in (old / "si").glob("lesson-*.json"):
        payload = json.loads(source.read_text(encoding="utf-8"))
        payload["subject"] = "Science"
        (sinhala_target / source.name).write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")


def append_ict_workbook() -> None:
    """Add the matching workbook activities to each ICT reading-book lesson."""
    reader = PdfReader(BOOKS / "ICT WB G-6 E.pdf")
    starts = [(1, 10), (2, 18), (3, 27), (4, 37), (5, 55), (6, 61)]
    for position, (number, first_pdf_page) in enumerate(starts):
        last_pdf_page = starts[position + 1][1] - 1 if position + 1 < len(starts) else len(reader.pages)
        output = TARGET / "en" / "ict" / f"lesson-{number}.json"
        payload = json.loads(output.read_text(encoding="utf-8"))
        for pdf_page in range(first_pdf_page, last_pdf_page + 1):
            text = clean(reader.pages[pdf_page - 1].extract_text() or "")
            if text:
                payload["chunks"].append({
                    "page": pdf_page, "index": 1,
                    "text": "ICT WORKBOOK ACTIVITY\n" + text,
                })
        payload["source"] = "ICT G-6 E PB.pdf + ICT WB G-6 E.pdf"
        output.write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")


def main() -> None:
    catalog = {"en": {}, "si": {}}
    for slug, subject, filename, lessons in SOURCES:
        built = write_book(slug, subject, filename, lessons)
        catalog["en"].setdefault(slug, {"subject": subject, "lessons": []})["lessons"].extend(built)
    append_ict_workbook()

    science_en = [
        "Wonders of the Living World", "Things Around Us", "Water as a Natural Resource",
        "Energy in Day-to-Day Life", "Light and Vision", "Sound and Hearing", "Magnets",
        "Electricity for a Comfortable Life", "Heat and Its Effects",
        "Food Related Interactions", "Weather and Climate",
    ]
    science_si = [
        "ජෛව ලෝකයේ අසිරිය", "අප අවට ඇති දේ", "ජලය ස්වාභාවික සම්පතක් ලෙස",
        "එදිනෙදා ජීවිතයේදී ශක්තිය", "ආලෝකය හා පෙනීම", "ශබ්දය හා ඇසීම",
        "චුම්බක", "සුවපහසු දිවියක් සඳහා විදුලිය", "තාපය හා එහි බලපෑම්",
        "ආහාර හා බැඳුණු අන්තර්ක්‍රියා", "කාලගුණය හා දේශගුණය",
    ]
    science_catalog = [{"number": i + 1, "title": title} for i, title in enumerate(science_en)]
    import_existing_science(science_catalog)
    catalog["en"]["science"] = {"subject": "Science", "lessons": science_catalog}
    catalog["si"]["science"] = {
        "subject": "Science",
        "lessons": [{"number": i + 1, "title": title} for i, title in enumerate(science_si)],
    }

    for language in catalog:
        for book in catalog[language].values():
            book["lessons"].sort(key=lambda item: item["number"])
    TARGET.mkdir(parents=True, exist_ok=True)
    (TARGET / "catalog.json").write_text(json.dumps(catalog, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"Catalog written with {sum(len(x['lessons']) for x in catalog['en'].values())} English lessons")


if __name__ == "__main__":
    main()
