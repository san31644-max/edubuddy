from __future__ import annotations
import hashlib, json, re, sys, time
from pathlib import Path
from urllib.parse import urljoin, urlparse
import requests
from bs4 import BeautifulSoup
from pypdf import PdfReader

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "uploads" / "syllabus"
OUT.mkdir(parents=True, exist_ok=True)
BASE = "https://e-thaksalawa.moe.gov.lk/lcms/"
CATEGORY = BASE + "course/index.php?categoryid=30"
MATCH = re.compile(r"text\s*book|work\s*book|syllabus|teacher.?s?\s*(instructional\s*)?(manual|guide)|පෙළ\s*පොත|විෂය\s*නිර්දේශ|ගුරු\s*මාර්ගෝපදේශ|பாடப்புத்தகம்|பாடநூல்|பாடத்திட்டம்|ஆசிரியர்\s*வழிகாட்டி", re.I)
session = requests.Session()
session.headers["User-Agent"] = "EduBuddySriLanka/1.0 educational importer; official sources only"

def soup(url: str) -> BeautifulSoup:
    r = session.get(url, timeout=45); r.raise_for_status()
    return BeautifulSoup(r.text, "html.parser")

def language(text: str) -> str:
    if re.search(r"[\u0D80-\u0DFF]", text): return "si"
    if re.search(r"[\u0B80-\u0BFF]", text): return "ta"
    return "en"

def safe_name(text: str) -> str:
    value = re.sub(r"[^A-Za-z0-9._-]+", "-", text).strip("-")[:90]
    return value or "grade6-resource"

category = soup(CATEGORY)
courses = []
for a in category.select('a[href*="course/view.php?id="]'):
    name = " ".join(a.get_text(" ", strip=True).split())
    href = urljoin(BASE, a.get("href"))
    if name and (name, href) not in courses: courses.append((name, href))

resources = []
for subject, course_url in courses:
    try: page = soup(course_url)
    except Exception as exc:
        print(f"Course skipped {course_url}: {exc}", file=sys.stderr); continue
    for a in page.select('a[href*="mod/resource/view.php"]'):
        title = " ".join(a.get_text(" ", strip=True).split())
        nearby = " ".join(a.parent.get_text(" ", strip=True).split()) if a.parent else title
        if MATCH.search(title + " " + nearby):
            key=(subject,title,urljoin(BASE,a.get('href')))
            if key not in resources: resources.append(key)

items=[]; seen=set()
for number,(subject,title,url) in enumerate(resources,1):
    try:
        r=session.get(url,timeout=90,allow_redirects=True);r.raise_for_status()
        if not (r.content[:5]==b"%PDF-" or "application/pdf" in r.headers.get("content-type", "")):
            wrapper=BeautifulSoup(r.text,"html.parser")
            candidates=[]
            for tag,attr in (("object","data"),("embed","src"),("iframe","src"),("a","href")):
                for node in wrapper.select(tag+"["+attr+"]"):
                    target=urljoin(r.url,node.get(attr))
                    if "pluginfile.php" in target or target.lower().split("?")[0].endswith(".pdf"):
                        candidates.append(target)
            meta=wrapper.select_one('meta[http-equiv="refresh" i]')
            if meta:
                found=re.search(r'url\s*=\s*[\"\']?([^\"\']+)',meta.get('content',''),re.I)
                if found:candidates.append(urljoin(r.url,found.group(1)))
            if not candidates: continue
            r=session.get(candidates[0],timeout=120,allow_redirects=True);r.raise_for_status()
        if not (r.content[:5]==b"%PDF-" or "application/pdf" in r.headers.get("content-type", "")): continue
        digest=hashlib.sha256(r.content).hexdigest()
        if digest in seen: continue
        seen.add(digest)
        filename=f"{number:03d}-{safe_name(subject)}-{safe_name(title)}-{digest[:10]}.pdf"
        path=OUT/filename;path.write_bytes(r.content)
        reader=PdfReader(str(path));chunks=[]
        for page_no,page in enumerate(reader.pages,1):
            text=" ".join((page.extract_text() or "").replace("\x00", " ").split())
            if not text: continue
            start=0;index=0
            while start<len(text):
                end=min(start+1800,len(text))
                if end<len(text):
                    cut=text.rfind(" ",start+1200,end)
                    if cut>start:end=cut
                part=text[start:end].strip()
                if len(part)>80:chunks.append({"page":page_no,"index":index,"text":part});index+=1
                start=end
        items.append({"subject":subject[:180],"language":language(subject+" "+title+(chunks[0]['text'] if chunks else '')),"title":title[:255],"url":r.url,"file":"uploads/syllabus/"+filename,"sha256":digest,"chunks":chunks})
        print(f"[{number}/{len(resources)}] {filename}: {len(chunks)} chunks")
        time.sleep(.15)
    except Exception as exc: print(f"Resource skipped {url}: {exc}",file=sys.stderr)
(OUT/"curriculum.json").write_text(json.dumps(items,ensure_ascii=False),encoding="utf-8")
print(json.dumps({"courses":len(courses),"candidate_resources":len(resources),"pdfs":len(items),"chunks":sum(len(x['chunks']) for x in items)}))
