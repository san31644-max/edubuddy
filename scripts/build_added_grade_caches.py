"""Build caches for newly supplied Grade 7-9 textbook folders."""
import json, re
from pathlib import Path
from pypdf import PdfReader
from pandukabhaya import Converter
ROOT=Path(__file__).resolve().parents[1];BASE=ROOT/'uploads'/'syllabus';TARGET=BASE/'textbook-cache';SINHALA=Converter('fm_abhaya')
CONFIG=[(7,'si','GRADE 7 SINHALA MEDIUM'),(7,'ta','GRADE 7 TAMIL MEDIUM'),(8,'en','grade 8 english medium'),(9,'en','GRADE 9 ENGLISH MEDIUM')]
def details(name):
 n=name.lower();rules=[('math','mathematics','Mathematics'),('science','science','Science'),('history','history','History'),('his ','history','History'),('geo','geography','Geography'),('civic','civic','Civic Education'),('health','health','Health and Physical Education'),('ict','ict','ICT'),('information communication','ict','ICT'),('english','english','English'),('en pb','english','English'),('sinhala','sinhala','Sinhala'),('second la','second-sinhala','Second Language Sinhala'),('bud','buddhism','Buddhism'),('pts','pts','Practical and Technical Skills')]
 for token,slug,subject in rules:
  if token in n:
   part=''
   if 'work' in n or ' wb' in n or '(1)' in n:part=' Workbook'
   elif 'p-ii' in n or 'part ii' in n or 'pii' in n:part=' Part II'
   elif 'p-i' in n or 'part i' in n:part=' Part I'
   elif 'reading' in n or n=='book.pdf':part=' Reading Book'
   return slug,subject,subject+part
def clean(text):
 text=text.replace('\u00ad',' ');text=re.sub(r'[ \t]+',' ',text);return re.sub(r'\n{3,}','\n\n',text).strip()
for grade,language,folder in CONFIG:
 catalog={};counters={}
 for pdf in sorted((BASE/folder).glob('*.pdf')):
  overrides={
   (7,'book (1).pdf'):('ict','ICT','ICT Activity Book'),
   (7,'book.pdf'):('ict','ICT','ICT Textbook'),
   (7,'book (2).pdf'):('civic','Civic Education','Civic Education'),
   (7,'book (3).pdf'):('health','Health and Physical Education','Health and Physical Education'),
   (8,'book.pdf'):('second-tamil','Second Language Tamil','Second Language Tamil'),
  }
  info=overrides.get((grade,pdf.name),details(pdf.name))
  if not info:print('Skipping unrecognized PDF:',pdf.name);continue
  slug,subject,title=info;counters[slug]=counters.get(slug,0)+1;number=counters[slug];reader=PdfReader(pdf);chunks=[]
  for page_number,page in enumerate(reader.pages,1):
   text=clean(page.extract_text() or '')
   if language=='si':text=clean(SINHALA.convert(text))
   if text:chunks.append({'page':page_number,'index':0,'text':text})
  out=TARGET/f'grade-{grade}'/language/slug;out.mkdir(parents=True,exist_ok=True)
  (out/f'lesson-{number}.json').write_text(json.dumps({'title':title,'subject':subject,'grade':grade,'language':language,'source':pdf.name,'chunks':chunks},ensure_ascii=False),encoding='utf-8')
  catalog.setdefault(slug,{'subject':subject,'lessons':[]})['lessons'].append({'number':number,'title':title});print(f'Grade {grade} {language}: {title} ({len(chunks)} pages)')
 out=TARGET/f'grade-{grade}'/language;out.mkdir(parents=True,exist_ok=True);(out/'catalog.json').write_text(json.dumps(catalog,ensure_ascii=False,indent=2),encoding='utf-8')
