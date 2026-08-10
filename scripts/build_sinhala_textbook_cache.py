"""Build readable Unicode Sinhala Grade 6 textbook lesson caches."""
import json,re
from pathlib import Path
from pypdf import PdfReader
from pandukabhaya import Converter

ROOT=Path(__file__).resolve().parents[1];BOOKS=ROOT/'uploads'/'syllabus'/'GRADE 6 SINHALA MEDIUM';TARGET=ROOT/'uploads'/'syllabus'/'textbook-cache';convert=Converter('fm_abhaya')

SOURCES=[
('buddhism','Buddhism','buddsim g-6 s.pdf',[(1,'අසිරිමත් බෝසත් කුමරු',1),(2,'සිදුහත් කුමරුගේ ළමා කාලය හා යොවුන් විය',7),(3,'තිලොවට ගුරු වූ බුදු සමිඳු',13),(4,'අපි පන්සල් යමු',24),(5,'පතුරමු සැමට මෙත් සිත්',31),(6,'රාහුල පොඩි හාමුදුරුවෝ',35),(7,'දියුණුවේ රන් දොරටු',41),(8,'දිවි මග සරු කරන සීලය',46),(9,'පන්සිල් රකිමු',50),(10,'මවු පිය ගුණ වරුණ',54),(11,'අභියෝග ජයගත් තරුණයෝ',60),(12,'අපේකම සුරකිමු',65),(13,'හොඳ නරක හැඳින යහපතෙහි යෙදෙමු',70),(14,'වෙනස් වීමට ඔරොත්තු දෙමු',75),(15,'වරදින් මිදෙමු',80),(16,'රකිනවිට පරිසරය - රැකෙනු ඇත අප සැම',84),(17,'මිහිඳු මාහිමි සමය වෙත යමු',90),(18,'ශාසනික දායාද සුරකිමු',96),(19,'අරපිරිමැස්ම දියුණුවට මගකි',101),(20,'රන් පිහාටු',106),(21,'සැදැහැ ගුණයෙන් සරු වෙමු',111)]),
('civic','Civic Education','civic 6 S.pdf',[(1,'අපේ පාසල',1),(2,'අප ජීවත්වන ප්‍රදේශය',47),(3,'යහපත් පුරවැසියෙකු වශයෙන් ප්‍රගුණ කළ යුතු ගුණාංග',72)]),
('geography','Geography','geo g-6 S.pdf',[(1,'පාසල හා අවට වටපිටාව',1),(2,'නිවස අවට වටපිටාවේ භූමියේ ස්වභාවය',22),(3,'තම නිවස අවට වටපිටාව යහපත්ව පවත්වා ගැනීම',37),(4,'ශ්‍රී ලංකාවේ පිහිටීම',55)]),
('health','Health and Physical Education','health g-6 S.pdf',[(1,'සුවෙන් සතුටින් ජීවත් වෙමු',1),(2,'අවශ්‍යතා හා ආශාවන් හඳුනා ගනිමු',40),(3,'නිවැරදි ඉරියව් මගින් පෞරුෂය වර්ධනය කර ගනිමු',48),(4,'විවේකය විනෝදයෙන් ගත කිරීමට ක්‍රීඩා කරමු',63),(5,'මලල ක්‍රීඩා හැකියා වර්ධනය සඳහා මූලික ක්‍රියාකාරකම්වල යෙදෙමු',80),(6,'නීති රීතිවලට ගරු කරමින් ක්‍රීඩා කරමු',91),(7,'සෞඛ්‍යවත් ජීවිතයකට නිවැරදි ආහාර පුරුදු ඇති කර ගනිමු',97),(8,'අපේ සිරුර නීරෝගීව තබා ගනිමු',111),(9,'සමබර ජීවිතයකට යෝග්‍යතා වර්ධනය කර ගනිමු',122),(10,'දැනුම්වත් වෙමු අභියෝග ජය ගනිමු',136)]),
('history','History','History G 06 Sinhala.pdf',[(1,'ඉතිහාසය හැඳින්වීම',1),(2,'ආදි මිනිසා',7),(3,'ලෝකයේ පැරණි ශිෂ්ටාචාර',20),(4,'ශ්‍රී ලංකාව ජනාවාස වීම',30),(5,'අපේ අභීත රජවරු',36)]),
('ict','ICT','ict pb g-6 S.pdf',[(1,'පරිගණකයේ වැදගත්කම',1),(2,'පරිගණක විද්‍යාගාරය ආරක්ෂිතව භාවිතය',14),(3,'මෙහෙයුම් පද්ධතිය හා ගොනු හැසිරවීම',27),(4,'යෙදුම් මෘදුකාංග භාවිතය සඳහා මූසිකය හා යතුරුපුවරුව යොදා ගැනීම',41),(5,'ඇල්ගොරිතම සහ ගැලීම් සටහන්',55),(6,'තොරතුරු රැස්කිරීම හා සන්නිවේදනය සඳහා අන්තර්ජාලය භාවිතය',64)]),
('mathematics','Mathematics','maths g6 p-I S.pdf',[(1,'වෘත්ත',1),(2,'ස්ථානීය අගය',8),(3,'පූර්ණ සංඛ්‍යා මත ගණිත කර්ම',21),(4,'කාලය',38),(5,'සංඛ්‍යා රේඛාව',61),(6,'නිමානය හා වටැයීම',74),(7,'කෝණ',84),(8,'දිශා',94),(9,'භාග',110),(10,'තේරීම',132),(11,'සාධක හා ගුණාකාර',137)]),
('mathematics','Mathematics','maths g-6 P-II S.pdf',[(12,'සරල රේඛීය තල රූප',1),(13,'දශම',10),(14,'සංඛ්‍යා වර්ග හා සංඛ්‍යා රටා',25),(15,'දිග',39),(16,'ද්‍රව මිනුම්',59),(17,'ඝන වස්තු',70),(18,'වීජීය සංකේත',86),(19,'වීජීය ප්‍රකාශන ගොඩනැගීම හා ආදේශය',91),(20,'ස්කන්ධය',97),(21,'අනුපාත',109),(22,'දත්ත රැස් කිරීම හා නිරූපණය',121),(23,'දත්ත අර්ථකථනය',133),(24,'දර්ශක',142),(25,'වර්ගඵලය',148)]),
('pts','Practical and Technical Skills','pts g-6 S.pdf',[(1,'කෘෂිකර්මය',1),(2,'ආහාර',36),(3,'ආරම්භක තාක්ෂණවේදය',63),(4,'ව්‍යාපාර කටයුතු',81),(5,'රූපණ',102)]),
('science','Science','science g-6 S.pdf',[(1,'ජෛව ලෝකයේ අසිරිය',1),(2,'අප අවට ඇති දේ',17),(3,'ජලය ස්වාභාවික සම්පතක් ලෙස',31),(4,'එදිනෙදා ජීවිතයේදී ශක්තිය',49),(5,'ආලෝකය හා පෙනීම',66),(6,'ශබ්දය හා ඇසීම',84),(7,'චුම්බක',101),(8,'සුවපහසු දිවියක් සඳහා විදුලිය',116),(9,'තාපය හා එහි බලපෑම්',134),(10,'ආහාර හා බැඳුණු අන්තර්ක්‍රියා',149),(11,'කාලගුණය හා දේශගුණය',160)]),
('sinhala','Sinhala','sinhala lan G-6.pdf',[(1,'නරි නයිදේ රැවටිච්චී',1),(2,'උදය සිරි',7),(3,'චීචෙං චුලඟෙං යමු කිරි නෑනෝ',11),(4,'අක්ෂරමාලාව හා පිලි',24),(5,'නාම පද හා එහි ප්‍රභේද',29),(6,'සිරිත් හොඳ දැන ගෙන',42),(7,'අලි උවදුර',45),(8,'නලපාන ජාතකය',51),(9,'ක්‍රියා පද හා එහි ප්‍රභේද',62)])]

def clean(text):
 text=text.replace('\u00ad',' ');text=re.sub(r'[ \t]+',' ',text);text=re.sub(r'\n{3,}','\n\n',text);return text.strip()
def build(slug,subject,filename,lessons):
 reader=PdfReader(BOOKS/filename);out=TARGET/'si'/slug;out.mkdir(parents=True,exist_ok=True);items=[]
 for pos,(number,title,printed) in enumerate(lessons):
  first=printed+10;last=(lessons[pos+1][2]+10-1) if pos+1<len(lessons) else len(reader.pages);chunks=[]
  for page in range(first,last+1):
   raw=clean(reader.pages[page-1].extract_text() or '');text=clean(convert.convert(raw))
   if text:chunks.append({'page':printed+(page-first),'index':0,'text':text})
  payload={'title':f'පාඩම {number}: {title}','subject':subject,'language':'si','source':filename,'chunks':chunks}
  (out/f'lesson-{number}.json').write_text(json.dumps(payload,ensure_ascii=False),encoding='utf-8');items.append({'number':number,'title':title});print(subject,number,len(chunks))
 return items
def append_ict_workbook():
 reader=PdfReader(BOOKS/'ict WB g-6 S (1).pdf');starts=[(1,10),(2,18),(3,27),(4,37),(5,55),(6,61)]
 for pos,(number,first) in enumerate(starts):
  last=starts[pos+1][1]-1 if pos+1<len(starts) else len(reader.pages);path=TARGET/'si'/'ict'/f'lesson-{number}.json';payload=json.loads(path.read_text(encoding='utf-8'))
  for page in range(first,last+1):
   text=clean(convert.convert(clean(reader.pages[page-1].extract_text() or '')))
   if text:payload['chunks'].append({'page':page,'index':1,'text':'ICT වැඩපොත් ක්‍රියාකාරකම\n'+text})
  payload['source']+=' + ict WB g-6 S (1).pdf';path.write_text(json.dumps(payload,ensure_ascii=False),encoding='utf-8')
def main():
 catalog=json.loads((TARGET/'catalog.json').read_text(encoding='utf-8'));catalog['si']={}
 for slug,subject,filename,lessons in SOURCES:catalog['si'].setdefault(slug,{'subject':subject,'lessons':[]})['lessons'].extend(build(slug,subject,filename,lessons))
 append_ict_workbook()
 for book in catalog['si'].values():book['lessons'].sort(key=lambda x:x['number'])
 (TARGET/'catalog.json').write_text(json.dumps(catalog,ensure_ascii=False,indent=2),encoding='utf-8');print('Sinhala lessons',sum(len(x['lessons']) for x in catalog['si'].values()))
if __name__=='__main__':main()
