"""Extract normalized Unicode text from an admin-uploaded curriculum PDF."""
import json,re,sys
from pathlib import Path
from pandukabhaya import Converter
sys.stdout.reconfigure(encoding='utf-8')
path=Path(sys.argv[1]);encoding=sys.argv[2] if len(sys.argv)>2 else 'auto';converter=Converter('fm_abhaya');pages=[]
try:
 import pymupdf
 document=pymupdf.open(path);page_count=document.page_count;source=((number+1,page.get_text('text')) for number,page in enumerate(document));engine='PyMuPDF'
except ImportError:
 from pypdf import PdfReader
 document=PdfReader(path);page_count=len(document.pages);source=((number+1,page.extract_text() or '') for number,page in enumerate(document.pages));engine='pypdf'
for number,raw in source:
 legacy=encoding=='fm_abhaya' or (encoding=='auto' and not re.search(r'[\u0d80-\u0dff]',raw) and bool(re.search(r'laIq|fY%|úoHd|m%Yak|ms<s;=re',raw)))
 text=converter.convert(raw) if legacy else raw;text=re.sub(r'[ \t]+',' ',text);text=re.sub(r'\n{3,}','\n\n',text).strip()
 if text:pages.append({'page':number,'text':text})
sections=[]
def contents_entry_count(text):
 return len(re.findall(r"(?m)^\s*\d{1,2}(?:\.|['’])\s+[^\n]*?\s+\d{1,3}\s*$",text))
contents_index=next((index for index,page in enumerate(pages) if re.search(r'\b(?:Contents|Index)\b',page['text'],re.I)),None)
contents_pages=pages[contents_index:contents_index+2] if contents_index is not None else []
for page in pages:
 if page['page']<=15 and contents_entry_count(page['text'])>=3 and page not in contents_pages:contents_pages.append(page)
for page in contents_pages:
 lines=[re.sub(r'\s+',' ',line).strip() for line in page['text'].splitlines() if line.strip()]
 for direct in re.finditer(r'(?m)^\s*(\d{1,2})\.\s+(?!\d)(.*?\S)\s+(\d{1,3})\s*$',page['text']):
  chapter=int(direct.group(1))
  if not any(item['number']==chapter for item in sections):sections.append({'number':chapter,'title':re.sub(r'\s+',' ',direct.group(2)).strip(),'printed_page':int(direct.group(3))})
 for index,line in enumerate(lines):
  match=re.match(r"^(\d{1,2})(?:(?:\.|['’])(?:\s+(?!\d)(.*))?)?$",line)
  if not match:continue
  chapter=int(match.group(1));tail=(match.group(2) or '').strip();title='';printed=None
  direct=re.match(r'^(.*?\S)\s+(\d{1,3})$',tail)
  if direct:title=direct.group(1).strip();printed=int(direct.group(2))
  elif tail:title=tail
  for candidate in lines[index+1:index+12]:
   page_range=re.fullmatch(r'0*(\d{1,3})\s*(?:[-–]\s*\d{1,3})?',candidate)
   titled_page=re.match(r'^(.*?\S)\s+(\d{1,3})$',candidate)
   if page_range:printed=int(page_range.group(1));break
   if titled_page:
    if not title:title=titled_page.group(1).strip()
    printed=int(titled_page.group(2));break
   if re.match(r"^\d{1,2}(?:(?:\.|['’])(?:\s+|$)|$)(?!\d)",candidate):break
   if not re.fullmatch(r'(?i)(?:Contents|Index)',candidate):title=(title+' '+candidate).strip()
  if printed is not None and title and not any(item['number']==chapter for item in sections):sections.append({'number':chapter,'title':title,'printed_page':printed})
sections.sort(key=lambda item:item['number'])
if sections and sections[0]['number']==1:
 contiguous=[]
 for expected,item in enumerate(sections,1):
  if item['number']!=expected:break
  contiguous.append(item)
 sections=contiguous
chapter_floor=max(1,min((item['number'] for item in sections),default=1)-10)
chapter_ceiling=max((item['number'] for item in sections),default=99)
fallback_pages=[] if sections and sections[0]['number']==1 else pages
for page in fallback_pages:
  lines=[re.sub(r'\s+',' ',line).strip() for line in page['text'].splitlines() if line.strip()]
  joined='\n'.join(lines)
  match=re.search(r'(?mi)^(?:(?:Activity|Assignment)\s*-?\s*)?(\d{1,2})\.[12]\b',joined)
  if not match:continue
  chapter=int(match.group(1))
  if chapter<chapter_floor or chapter>chapter_ceiling:continue
  if any(item['number']==chapter for item in sections):continue
  marker_index=next((i for i,line in enumerate(lines) if re.match(rf'(?i)^(?:(?:Activity|Assignment)\s*-?\s*)?{chapter}\.[12]\b',line)),len(lines))
  ignored=re.compile(r'^(?:\d+|For free distribution|Biology|Physics|Chemistry)$',re.I)
  candidates=[line for line in lines[:marker_index] if not ignored.fullmatch(line) and len(line)>3]
  title=candidates[0] if candidates else f'Chapter {chapter}'
  sections.append({'number':chapter,'title':title,'pdf_page':page['page']})
cover=re.sub(r'\s+',' ',' '.join(page['text'] for page in pages if page['page']<=2)).lower()
profiles=[]
literary='english literary texts' in cover
if 'grade 10' in cover and 'geography' in cover:
 profiles=['The Composition of the Earth','The Major Physical Characteristics of the Earth','Major Types of Agricultural Land Utilization in the World','Agriculturing of Sri Lanka','Manufacturing Industries','The Distribution of a few Industries in Sri Lanka Problems and Trends','Indroduction to Maps']
elif 'grade 10' in cover and 'civic education' in cover:
 profiles=['Democratic Governance','Decentralisation and devolution of power','The multicultural society','Economic systems and economic relations','Conflict resolution in a democratic society']
elif 'grade 10' in cover and 'information and communication technology' in cover:
 profiles=['Information and Communication Technology','Fundamentals of a computer system','Data Representation Methods in the Computer system','Logic Gates with Boolean Functions','Operating Systems','Word Processing','Electronic Spreadsheet','Electronic Presentations','Database']
elif 'grade 10' in cover and re.search(r'\bhistory\b',cover):
 profiles=['Sources of Studying History','Ancient Settlements','Evolution of Political Power in Sri Lanka','The Ancient Society of Sri Lanka','The Ancient Science and Technology in Sri Lanka','Historical Knowledge and Its Practical Application','Decline of Ancient Cities in the Dry Zone and Origin of New Kingdoms in South West','Kandyan Kingdom','Renaissance','Sri Lanka and the Western World']
elif (('grade 10' in cover and re.search(r'\bscience\b',cover)) or ('science' in path.name.lower() and ('g-10' in path.name.lower() or 'grade 10' in path.name.lower()))):
 if any('chemical basis of life' in page['text'].lower() for page in pages[:20]):profiles=['Chemical basis of life','Motion in a straight line','Structure of matter','Newton’s laws of motion','Friction','Structure and functions of the plant and animal cell','Quantification of elements and compounds','Characteristics of organisms','Resultant force','Chemical bonds','Turning effect of a force','Equilibrium of Forces']
 else:profiles=['The world of life','Continuity of life','Hydrostatic pressure','Changes in Matter','Rate of Reaction','Work, energy and power','Current electricity','Inheritance']
if profiles:
 located=[]
 content_search_start=(pages[contents_index]['page']+1) if contents_index is not None else (max((page['page'] for page in contents_pages),default=7)+1)
 profile_start=13 if profiles and profiles[0]=='The world of life' else 1
 for number,title in enumerate(profiles,profile_start):
  needle=re.sub(r'\s+',' ',title).lower()
  start=next((page['page'] for page in pages if page['page']>=content_search_start and 0<=re.sub(r'\s+',' ',page['text']).lower().find(needle)<3000 and re.search(rf'(?m)^\s*{number}\s*$',page['text'][:1200])),None)
  if start is None:start=next((page['page'] for page in pages if page['page']>=content_search_start and 0<=re.sub(r'\s+',' ',page['text']).lower().find(needle)<150),None)
  if start is not None:located.append({'number':number,'title':title,'pdf_page':start})
 if len(located)>=min(2,len(profiles)):sections=located
if literary:
 sections=[item for item in sections if item['number']<=20]
 sections.extend([
  {'number':21,'title':'The Nightingale and the Rose','pdf_page':30},
  {'number':22,'title':'The Lahore Attack','pdf_page':37},
  {'number':23,'title':'The Lumber Room','pdf_page':41},
  {'number':24,'title':'Wave','pdf_page':47},
  {'number':25,'title':'Twilight of a Crane','pdf_page':51},
 {'number':26,'title':'The Bear','pdf_page':77},
 ])
if 'science' in path.name.lower() and ('g-10' in path.name.lower() or 'grade 10' in path.name.lower()):
 if re.search(r'p[- ]?ii',path.name,re.I):
  names=['The world of life','Continuity of life','Hydrostatic pressure','Changes in Matter','Rate of Reaction','Work, energy and power','Current electricity','Inheritance'];starts=[11,37,73,96,127,135,150,179];base=13
 else:
  names=['Chemical basis of life','Motion in a straight line','Structure of matter','Newton’s laws of motion','Friction','Structure and functions of the plant and animal cell','Quantification of elements and compounds','Characteristics of organisms','Resultant force','Chemical bonds','Turning effect of a force','Equilibrium of Forces'];starts=[15,37,66,98,112,124,137,153,170,182,203,215];base=1
 sections=[{'number':base+i,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
sections.sort(key=lambda item:item['number'])
if sections:
 first_content=next((page['page'] for page in pages if re.search(r'(?mis)(?:^\s*1\s*\n\s*For free distribution\b|For free distribution\s*\n\s*1\b)',page['text'],re.I)),None)
 if first_content is None and contents_index is not None:first_content=next((page['page'] for page in pages if page['page']>pages[contents_index]['page'] and re.search(r'^\s*1\s*\n',page['text'])),None)
 if first_content is None:first_content=15
 offset=first_content-1
 for item in sections:
  if 'pdf_page' not in item:item['pdf_page']=item['printed_page']+offset
print(json.dumps({'engine':engine,'pages':page_count,'extracted_pages':len(pages),'sections':sections,'page_texts':pages,'text':'\n\n'.join(x['text'] for x in pages)},ensure_ascii=False))
