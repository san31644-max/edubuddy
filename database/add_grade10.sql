START TRANSACTION;
INSERT INTO grades(grade_number,name,status) VALUES(10,'Grade 10','active') ON DUPLICATE KEY UPDATE name=VALUES(name),status='active';
SET @grade10=(SELECT id FROM grades WHERE grade_number=10);
INSERT INTO subjects(grade_id,subject_code,name_en,name_si,name_ta,icon,status) VALUES
(@grade10,'mathematics','Mathematics','ගණිතය','கணிதம்','➗','active'),
(@grade10,'science','Science','විද්‍යාව','விஞ்ஞானம்','🔬','active'),
(@grade10,'sinhala','Sinhala Language & Literature','සිංහල භාෂාව හා සාහිත්‍යය','சிங்கள மொழி மற்றும் இலக்கியம்','📖','active'),
(@grade10,'tamil','Tamil Language & Literature','දෙමළ භාෂාව හා සාහිත්‍යය','தமிழ் மொழியும் இலக்கியமும்','📖','active'),
(@grade10,'english','English','ඉංග්‍රීසි','ஆங்கிலம்','🔤','active'),
(@grade10,'history','History','ඉතිහාසය','வரலாறு','🏛️','active'),
(@grade10,'geography','Geography','භූගෝල විද්‍යාව','புவியியல்','🌍','active'),
(@grade10,'ict','Information & Communication Technology','තොරතුරු හා සන්නිවේදන තාක්ෂණය','தகவல் தொடர்பாடல் தொழில்நுட்பம்','💻','active'),
(@grade10,'health','Health & Physical Education','සෞඛ්‍ය හා ශාරීරික අධ්‍යාපනය','சுகாதாரமும் உடற்கல்வியும்','🏃','active'),
(@grade10,'civic','Civic Education','පුරවැසි අධ්‍යාපනය','குடியியற் கல்வி','🤝','active'),
(@grade10,'business-accounting','Business & Accounting Studies','ව්‍යාපාර හා ගිණුම්කරණ අධ්‍යයනය','வணிகமும் கணக்கீட்டுக் கல்வியும்','📊','active')
ON DUPLICATE KEY UPDATE name_en=VALUES(name_en),name_si=VALUES(name_si),name_ta=VALUES(name_ta),icon=VALUES(icon),status='active';
COMMIT;
