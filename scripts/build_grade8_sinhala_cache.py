"""Split every completed Grade 8 Sinhala-medium textbook into real lessons."""
import json,re,sys
from pathlib import Path
from pypdf import PdfReader
from pandukabhaya import Converter

ROOT=Path(__file__).resolve().parents[1];BOOKS=ROOT/'uploads'/'syllabus'/'grade 8 sinhala medium';TARGET=ROOT/'uploads'/'syllabus'/'textbook-cache'/'grade-8'/'si'
CONVERT=Converter('fm_abhaya')
sys.stdout.reconfigure(encoding='utf-8')
BOOKS_INFO={
 'bud g-8.pdf':('buddhism','Buddhism'),
 'civic g-8 S.pdf':('civic','Civic Education'),
 'geo 8 S (1).pdf':('geography','Geography'),
 'history G-8 S.pdf':('history','History'),
 'maths g-8 p-I S.pdf':('mathematics','Mathematics'),
 'maths g-8 p-II S.pdf':('mathematics','Mathematics'),
 'PTS Grade 8 - Inner Pages_CTP.pdf':('pts','Practical and Technical Skills'),
 'science P-I G8 S.pdf':('science','Science'),
 'science 8 II S.pdf':('science','Science'),
 'sinhala G-8.pdf':('sinhala','Sinhala'),
 'Grade 8 - ICT_CTP.pdf':('ict','ICT'),
 'Information Communication_G8 S.pdf':('ict','ICT'),
 'book.pdf':('second-tamil','Second Language Tamil'),
}
MANUAL={
 'history G-8 S.pdf':[
  (1,'ශ්‍රී ලංකාවේ සාම්ප්‍රදායික තාක්ෂණය හා කලාව',1),(2,'මහනුවර රාජධානිය',31),
  (3,'යුරෝපයේ පුනරුදය',40),(4,'දේශ ගවේෂණ හා යුරෝපා ජාතීන් පෙරදිගට පැමිණීම',55),
  (5,'ශ්‍රී ලංකාවේ මුහුදුබඩ ප්‍රදේශ පෘතුගීසීන් යටතට පත් වීම',65),
 ],
 'civic g-8 S.pdf':[
  (1,'පොදු සේවා',1),(2,'ප්‍රජාතන්ත්‍රවාදී ජන සමාජය',34),(3,'බහු සංස්කෘතික ජන සමාජය',54),
  (4,'කාලීන ගැටලු',74),(5,'ගැටලු විසඳමින් අභියෝග ජය ගනිමු',112),(6,'වැඩ ලෝකයට පිවිසෙමු',126),
 ],
 'sinhala G-8.pdf':[
  (1,'එතනහාමි',1),(2,'රබීන්ද්‍රනාත් තාගෝර් හා ඔහුගේ නිර්මාණ',11),(3,'අලුත් අවුරුදු උත්සවය',20),
  (4,'ශබ්දකෝෂ භාවිතය',30),(5,'රාත්‍රිය',38),(6,'කතා බහට අවධානය',47),(7,'විධි ක්‍රියා - ආශීර්වාද ක්‍රියා',52),
  (8,'ප්‍රායෝගික ලේඛන',59),(9,'අවුරුදු සිහිනය',66),(10,'සැබෑ ළෙන්ගතුකම',78),(11,'සිරි ලංකා රට ම අපි',89),
  (12,'නිවැරදිව කියවමු',93),(13,'වාක්‍ය රීති I',104),(14,'කතරගම පුද බිම',111),(15,'පරිසර හිතකාමි කඩදාසි',118),
  (16,'අද බැරි නම් හෙට තෝරාපන් යාළු',123),(17,'පරාස්ස',132),(18,'වාක්‍ය රීති II',144),(19,'ප්‍රවෘත්ති',151),
  (20,'බොදු පුනරුදයේ සෙන්පතියාණෝ',159),(21,'මෙත් සිත සව් සත කෙරෙහි කරන්නේ',168),
  (22,'සයුරු ගැබෙන් මතු වූ නැව',176),(23,'සවන් යොමමු, කතා කරමු, ලියුම් ලියමු',183),
 ],
}
def clean(value):
 value=value.replace('\u00ad',' ');value=re.sub(r'[ \t]+',' ',value);return re.sub(r'\n{3,}','\n\n',value).strip()
def normalized(value):return re.sub(r'[^\w\u0d80-\u0dff\u0b80-\u0bff]+','',value,flags=re.UNICODE).lower()
def toc_entries(pages):
 entries=[];started=False;pending=None;toc_last=-1;toc_start=-1
 for page_index,text in enumerate(pages[:25]):
  lines=[clean(x) for x in text.splitlines() if clean(x)]
  if any('පටුන' in x or 'உள்ளடக்கம்' in x for x in lines):started=True;toc_start=page_index
  if not started:continue
  if page_index>toc_start+3:break
  found=0
  for line in lines:
   start=re.match(r'^(\d{1,2})\s*[.)-]\s*(.*)$',line)
   if start:
    if pending: pending=None
    pending=[int(start.group(1)),start.group(2).strip()]
   elif pending:pending[1]+=' '+line
   if pending:
    # Printed page number is followed by a competency such as 8.1.1, or is the final token.
    end=re.match(r'^(.*?\D)\s+(\d{1,3})(?:\s+\d+[.:]\d+(?:[.:]\d+)?)?\s*$',pending[1])
    if end and clean(end.group(1)):
     entries.append({'number':pending[0],'title':clean(end.group(1)),'printed':int(end.group(2))});pending=None;found+=1
  if found:toc_last=page_index
 # Remove duplicates and reject false front-matter numbering.
 out=[];seen=set()
 for item in entries:
  if item['number'] not in seen and item['number']<=80 and len(item['title'])<=120:
   seen.add(item['number']);out.append(item)
 if len(out)<2 or any(out[i]['number']!=out[i-1]['number']+1 for i in range(1,len(out))):return [],toc_last
 return out,toc_last
def locate(entries,pages,toc_last):
 offset=max(toc_last+1,0)-(entries[0]['printed']-1)
 return [max(toc_last+1,min(len(pages)-1,offset+item['printed']-1)) for item in entries]
only=sys.argv[1].lower() if len(sys.argv)>1 else ''
catalog=json.loads((TARGET/'catalog.json').read_text(encoding='utf-8')) if only and (TARGET/'catalog.json').is_file() else {}
next_number={};used_sources=[];reset_slugs=set()
for filename,(slug,subject) in BOOKS_INFO.items():
 if only and only not in filename.lower() and only not in slug.lower():continue
 if slug not in reset_slugs:catalog.pop(slug,None);reset_slugs.add(slug)
 path=BOOKS/filename
 if not path.is_file():continue
 reader=PdfReader(path);pages=[]
 for page in reader.pages:
  raw=page.extract_text() or ''
  pages.append(clean(raw if slug=='second-tamil' else CONVERT.convert(raw)))
 if filename in MANUAL:
  entries=[{'number':n,'title':title,'printed':printed} for n,title,printed in MANUAL[filename]]
  starts=[min(len(pages)-1,printed+9) for _,_,printed in MANUAL[filename]]
  toc_last=-1
 else:entries,toc_last=toc_entries(pages)
 if not entries:
  # PDFs with reliable bookmarks still get genuine chapter boundaries.
  outline=[]
  def walk(items):
   for item in items:
    if isinstance(item,list):walk(item)
    else:
     try:
      title=str(item.title);page=reader.get_destination_page_number(item)
      if re.search(r'chapter|chap\s*\d',title,re.I):outline.append((title,page))
     except Exception:pass
  try:walk(reader.outline)
  except Exception:pass
  entries=[{'number':i+1,'title':title,'printed':page+1} for i,(title,page) in enumerate(outline)];starts=[page for _,page in outline]
 elif filename not in MANUAL:starts=locate(entries,pages,toc_last)
 if not entries:
  first=10 if len(pages)>20 else 0
  count=next_number.get(slug,0) if slug=='ict' and next_number.get(slug,0) else max(2,round((len(pages)-first)/14))
  starts=[first+round(i*(len(pages)-first)/count) for i in range(count)]
  label='பாடம்' if slug=='second-tamil' else 'පාඩම'
  entries=[{'number':i+1,'title':f'{label} {i+1}','printed':starts[i]+1} for i in range(count)]
 base=next_number.get(slug,0)
 # Two ICT books describe the same six chapters; append workbook pages to them.
 merge=slug=='ict' and base and len(entries)==base
 for pos,item in enumerate(entries):
  number=item['number'] if not base or merge else base+pos+1
  first=starts[pos];last=(starts[pos+1]-1 if pos+1<len(starts) else len(pages)-1)
  chunks=[{'page':i+1,'index':1 if merge else 0,'text':pages[i]} for i in range(first,last+1) if pages[i]]
  out=TARGET/slug/f'lesson-{number}.json';out.parent.mkdir(parents=True,exist_ok=True)
  if merge and out.is_file():
   payload=json.loads(out.read_text(encoding='utf-8'));payload['chunks'].extend(chunks);payload['source']+=' + '+filename
  else:payload={'title':item['title'],'subject':subject,'grade':8,'language':'si','source':filename,'chunks':chunks}
  out.write_text(json.dumps(payload,ensure_ascii=False),encoding='utf-8')
  catalog.setdefault(slug,{'subject':subject,'lessons':[]})
  if not merge:catalog[slug]['lessons'].append({'number':number,'title':item['title']})
  print(f'{subject} {number}: {item["title"]} ({len(chunks)} pages)')
 if not merge:next_number[slug]=base+len(entries)
 used_sources.append(filename)
for book in catalog.values():book['lessons'].sort(key=lambda x:x['number'])
TARGET.mkdir(parents=True,exist_ok=True);(TARGET/'catalog.json').write_text(json.dumps(catalog,ensure_ascii=False,indent=2),encoding='utf-8')
print('Built',sum(len(x['lessons']) for x in catalog.values()),'Grade 8 Sinhala lessons from',len(used_sources),'PDFs')
