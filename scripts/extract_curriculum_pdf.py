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
contents_index=next((index for index,page in enumerate(pages) if re.search(r'(?:\b(?:Contents|Index)\b|පටුන)',page['text'],re.I)),None)
contents_pages=pages[contents_index:contents_index+2] if contents_index is not None else []
for page in pages:
 if page['page']<=15 and contents_entry_count(page['text'])>=3 and page not in contents_pages:contents_pages.append(page)
for page in contents_pages:
 lines=[re.sub(r'\s+',' ',line).strip() for line in page['text'].splitlines() if line.strip()]
 for direct in re.finditer(r'(?m)^\s*(\d{1,2})\.\s+(?!\d)(.*?\S)\s+(\d{1,3})\s*$',page['text']):
  chapter=int(direct.group(1))
  if not any(item['number']==chapter for item in sections):sections.append({'number':chapter,'title':re.sub(r'\s+',' ',direct.group(2)).strip(),'printed_page':int(direct.group(3))})
 for index,line in enumerate(lines):
  sinhala_entry=re.match(r'^(\d{1,2})ග\s+(.+)$',line)
  if sinhala_entry:
   chapter=int(sinhala_entry.group(1));title=sinhala_entry.group(2).strip();printed=None
   for candidate in lines[index+1:index+5]:
    if re.fullmatch(r'\d{1,3}',candidate):printed=int(candidate);break
    if re.match(r'^\d{1,2}ග\s+',candidate):break
   if printed is not None and not any(item['number']==chapter for item in sections):sections.append({'number':chapter,'title':title,'printed_page':printed})
   continue
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
# Grade 7 Sinhala books use several legacy Sinhala fonts and irregular
# contents-page layouts. Their official chapter starts are kept here so split
# volumes import as lessons rather than as one whole-book placeholder.
grade7_sinhala=encoding=='fm_abhaya' and re.search(r'(?:g[- ]?7|grade\s*7|\b7\b)',path.name,re.I)
grade11_sinhala=encoding=='fm_abhaya' and re.search(r'(?:g[- ]?11|grade\s*11|\b11\b)',path.name,re.I)
if grade11_sinhala and re.search(r'bud',path.name,re.I) and not sections:
 located=[]
 for page in pages:
  lines=[re.sub(r'\s+',' ',line).strip() for line in page['text'].splitlines() if line.strip()]
  for index,line in enumerate(lines[:5]):
   if not re.fullmatch(r'0\d|[12]\d|30',line):continue
   number=int(line)
   if number<1 or number>30 or (located and number!=located[-1]['number']+1):continue
   candidates=[x for x in lines[max(0,index-2):index] if not re.fullmatch(r'\d{1,3}',x) and len(x)>3]
   if not candidates:continue
   located.append({'number':number,'title':candidates[-1],'pdf_page':page['page']});break
 if len(located)>=10:sections=located
if grade7_sinhala and re.search(r'bud',path.name,re.I):
 located=[]
 for page in pages:
  lines=[line.strip() for line in page['text'].splitlines() if line.strip()]
  if len(lines)>=3 and re.fullmatch(r'\d{1,3}',lines[0]) and re.fullmatch(r'\d{1,2}',lines[1]):
   number=int(lines[1])
   if 1<=number<=30 and (not located or number==located[-1]['number']+1):located.append({'number':number,'title':lines[2],'pdf_page':page['page']})
 if len(located)>=15:sections=located
elif grade7_sinhala and re.search(r'(?:his|history)',path.name,re.I):
 names=['අපේ පැරණි ජන ජීවිතය','අපේ කීර්තිමත් රජවරු','අපේ සංස්කෘතික උරුමය','පසුකාලීන පාලන මධ්‍යස්ථාන','යුරෝපයේ පැරණි ශිෂ්ටාචාර'];starts=[11,26,45,63,72]
 sections=[{'number':i+1,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
elif grade7_sinhala and re.search(r'science.*(?:p[- ]?ii|pii)',path.name,re.I):
 names=['ධ්වනි ජනනය','ජෛව ක්‍රියාවලි','වායුගෝලය','තාපය හා උෂ්ණත්වය','පස','බලය හා චලිතය','ආහාර සහ පෝෂක','ඛනිජ හා පාෂාණ','ශක්ති ප්‍රභව'];starts=[11,22,39,51,71,85,98,113,126]
 sections=[{'number':11+i,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
elif grade7_sinhala and re.search(r'sinhala\s*la',path.name,re.I):
 names=['ගොවි බිරියගේ නුවණ','සසුන බැබළවූ සරණංකර සඟරජ තෙරණුවෝ','අක්ෂර මාලාව හා අක්ෂර වින්‍යාසය','ඔයි රයිරේ ඔයි රාමා','ශ්‍රී ලංකාවේ අහිකුණ්ඨික ජනතාව','මේ කාගේ දෝ කමතා','දෙව්දම් ඇදුරු එඩ්මන්ඩ් පීරිස් රදගුරු හිමි','කතා බහට සංයමයක්','තිලමුට්ඨි ජාතකය','අපේ ලෝහ තාක්ෂණයේ යටගියාව','ප්‍රායෝගික ලේඛන','සිනා කුමරි','රස කවි ගොතමු කවි රස විඳිමු','නිවේදන','ලොවට තර්ජනයක් වන විද්‍යුත් අපද්‍රව්‍ය','සකි සඳ පෙනේ සමනොළ ගල නැගෙනහිර','සුදන ගුණ','මා දුටු මුල් හිරු පෙන්නූ ලංකා','දුං දුමටයි අඟුරු මටයි','සකස් කඩ නො කී කට','පුස්තකාලයට පිය නගමු','තොප්පි වෙළෙන්දා','ළමා නාට්‍ය පිටපත් රචනය','යහළුවෝ']
 printed=[1,8,14,28,45,51,57,68,74,80,87,92,95,114,121,129,138,143,146,154,163,167,180,182]
 sections=[{'number':i+1,'title':title,'pdf_page':printed[i]+12} for i,title in enumerate(names)]
if grade7_sinhala and re.search(r'math',path.name,re.I):sections=[item for item in sections if item['number']<=29]
# Grade 9 Sinhala split-volume profiles.
grade9_sinhala=encoding=='fm_abhaya' and re.search(r'(?:g[- ]?9|grade\s*9|\b9\b)',path.name,re.I)
if grade9_sinhala and re.search(r'math',path.name,re.I):
 if re.search(r'p[- ]?(?:iii|3)',path.name,re.I):names=['අසමානතා','කුලක','වර්ගඵලය','සම්භාවිතාව','බහු අස්‍රවල කෝණ','වීජීය භාග','පරිමාණ රූප','දත්ත නිරූපණය හා අර්ථකථනය'];starts=[9,17,36,50,59,73,86,100];base=21
 elif re.search(r'p[- ]?(?:ii|2)',path.name,re.I):names=['අනුලෝම සමානුපාත','ගණකය','දර්ශක','වටැයීම හා විද්‍යාත්මක අංකනය','පථ හා නිර්මාණ','සමීකරණ','ත්‍රිකෝණයක කෝණ','සූත්‍ර','වෘත්තයක පරිධිය','පයිතගරස් සම්බන්ධය','ප්‍රස්තාර'];starts=[9,20,28,39,55,79,89,103,111,121,131];base=10
 else:names=['සංඛ්‍යා රටා','ද්විමය සංඛ්‍යා','භාග','ප්‍රතිශත','වීජීය ප්‍රකාශන','වීජීය ප්‍රකාශනවල සාධක','ප්‍රත්‍යක්ෂ','සරල රේඛා හා සමාන්තර රේඛා ආශ්‍රිත කෝණ','ද්‍රව මිනුම්'];starts=[9,24,35,49,66,75,87,100,124];base=1
 sections=[{'number':base+i,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
elif grade9_sinhala and re.search(r'science.*p[- ]?ii',path.name,re.I):
 names=['විද්‍යුත් විච්ඡේදනය','ඝනත්වය','ජෛව විවිධත්වය','කෘත්‍රිම පරිසරය හා හරිත සංකල්පය','තරංග පරාවර්තනය හා වර්තනය','සරල යන්ත්‍ර','නැනෝ තාක්ෂණය හා එහි භාවිත','අකුණු අනතුරු','ස්වාභාවික ආපදා','ස්වාභාවික සම්පත් තිරසරව භාවිතය'];starts=[11,20,29,52,68,94,113,129,139,161]
 sections=[{'number':10+i,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
elif grade9_sinhala and re.search(r'^geo',path.name,re.I):
 names=['ආසියානු ප්‍රදේශය','ශ්‍රී ලංකාවේ භූ දර්ශනය','ශ්‍රී ලංකාවේ සංවර්ධනයේ අවකාශීය වෙනස්කම්','පාරිසරික තුලිතතාව','ශ්‍රී ලංකා 1:50,000 භූ ලක්ෂණ සිතියම් කියවීම'];starts=[12,34,78,86,94]
 sections=[{'number':i+1,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
elif grade9_sinhala and re.search(r'^sinhala',path.name,re.I):
 names=['ශ්‍රී පාදය වැන්දවූ හැටි','ලප සේ සඳ විලසේ','දේශීය නර්තනයේ රන් සලකුණ','තොරතුරු ඉදිරිපත් කිරීම සඳහා බහුවිධ මාධ්‍ය ඇසුරු කරමු','සිංහල අක්ෂර මාලාව','මගේ රට මට අගෙයි','දෑසේ නිල් පැහැය','කවියක රස විඳ නව කව් පබඳිමු','ප්‍රායෝගික ලේඛන','නවම් මහේ නව වළල්ල','උගන්නැ සියබස - මත් වන්නැ එහි රසයෙන්','ආනන්ද කුමාරස්වාමි','හොඳ ළමයා නුවණිනී - හොඳ දේ තෝරා ගනී','සිරම්බි අඩිය','දිට්ඨ දාන','අභ්‍යවකාශ තාක්ෂණය','පොතපතෙන් සරු දහම්','රස විඳිනා දැනුම මනා','මහාසාර පලඳනාව','අපිත් නාට්‍යයක් රචනා කරමු'];printed=[1,9,15,24,31,49,59,63,76,87,94,103,115,139,145,156,165,179,194,208]
 sections=[{'number':i+1,'title':title,'pdf_page':printed[i]+11} for i,title in enumerate(names)]
elif grade9_sinhala and re.search(r'second\s*lang\s*tamil',path.name,re.I):
 starts=[11,30,42,55,72,84,96,105,118,128,143,156]
 sections=[{'number':i+1,'title':f'දෙවන භාෂාව දෙමළ - පාඩම {i+1}','pdf_page':start} for i,start in enumerate(starts)]
elif grade9_sinhala and re.search(r'^pts',path.name,re.I):
 names=['තාක්ෂණික ක්ෂේත්‍රය - කෘෂිකර්මය','තාක්ෂණික ක්ෂේත්‍රය - ආහාර','තාක්ෂණික ක්ෂේත්‍රය - ආරම්භක තාක්ෂණවේදය','තාක්ෂණික ක්ෂේත්‍රය - ව්‍යාපාර'];starts=[13,53,87,117]
 sections=[{'number':i+1,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
elif grade9_sinhala and re.search(r'^history',path.name,re.I):
 names=['ශ්‍රී ලංකාවේ මුහුදුබඩ ප්‍රදේශ ලන්දේසීන් යටතට පත්වීම','ශ්‍රී ලංකාවේ බ්‍රිතාන්‍ය බලය','ශ්‍රී ලංකාවේ ආගමික හා ජාතික පුනර්ජීවනය','ඉන්දියානු නිදහස් සටන් ව්‍යාපාරය','ශ්‍රී ලංකාවේ ආණ්ඩුක්‍රම ප්‍රතිසංස්කරණ හා ජාතික නිදහස් ව්‍යාපාරය','නිදහසින් පසු ශ්‍රී ලංකාව'];starts=[11,31,63,79,107,125]
 sections=[{'number':i+1,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
sections.sort(key=lambda item:item['number'])
grade11_science=encoding=='fm_abhaya' and 'science' in path.name.lower() and re.search(r'(?:g[-_ ]?11|grade[-_ ]*11)',path.name,re.I)
if grade11_science:
 located=[]
 for page in pages:
  lines=[re.sub(r'\s+',' ',line).strip() for line in page['text'].splitlines() if line.strip()]
  marker=next((index for index,line in enumerate(lines[:10]) if re.fullmatch(r'(?:0[1-9]|[12]\d|30)',line)),None)
  if marker is None:continue
  number=int(lines[marker])
  if any(item['number']==number for item in located):continue
  candidates=[line for line in lines[max(0,marker-4):marker] if not re.fullmatch(r'\d{1,3}',line) and len(line)>3]
  if not candidates:continue
  title=candidates[-1]
  located.append({'number':number,'title':title,'pdf_page':page['page']})
 if len(located)>=2:sections=sorted(located,key=lambda item:item['number'])
 if 'part2' in path.name.lower():
  names=['ජීවී පටක','ප්‍රභාසංශ්ලේෂණය','මිශ්‍රණ','තරංග සහ ඒවායේ යෙදීම්','ප්‍රකාශ විද්‍යාව','මානව දේහ ක්‍රියාවලි','අම්ල, භස්ම හා ලවණ','රසායනික ප්‍රතික්‍රියා ආශ්‍රිත තාප විපර්යාස']
  starts=[13,31,41,80,114,152,204,216]
  sections=[{'number':i+1,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
 elif 'part1' in path.name.lower():
  names=['තාපය','විද්‍යුත් උපකරණවල ජවය හා ශක්තිය','ඉලෙක්ට්‍රොනික විද්‍යාව','විද්‍යුත් රසායනය','විද්‍යුත් චුම්බකත්වය සහ විද්‍යුත් චුම්බක ප්‍රේරණය','හයිඩ්‍රොකාබන හා ඒවායේ ව්‍යුත්පන්න','ජෛවගෝලය']
  starts=[13,43,61,90,126,162,179]
  sections=[{'number':9+i,'title':title,'pdf_page':starts[i]} for i,title in enumerate(names)]
if sections:
 first_content=next((page['page'] for page in pages if re.search(r'(?mis)(?:^\s*1\s*\n\s*For free distribution\b|For free distribution\s*\n\s*1\b)',page['text'],re.I)),None)
 if first_content is None and contents_index is not None:first_content=next((page['page'] for page in pages if page['page']>pages[contents_index]['page'] and re.search(r'^\s*1\s*\n',page['text'])),None)
 if first_content is None:first_content=15
 offset=first_content-1
 for item in sections:
  if 'pdf_page' not in item:item['pdf_page']=item['printed_page']+offset
print(json.dumps({'engine':engine,'pages':page_count,'extracted_pages':len(pages),'sections':sections,'page_texts':pages,'text':'\n\n'.join(x['text'] for x in pages)},ensure_ascii=False))
