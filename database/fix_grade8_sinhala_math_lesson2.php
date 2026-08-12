<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit;
require_once __DIR__.'/../includes/db.php';

$notes = <<<'TEXT'
අධ්‍යයන සටහන්

පාඩම: පරිමිතිය
විෂය: ගණිතය — 8 ශ්‍රේණිය

1. පරිමිතිය යනු තල රූපයක පිටත මායිමේ මුළු දිගයි.

2. සමචතුරස්‍රයක පරිමිතිය
   P = 4 × පැත්තක දිග
   උදාහරණය: පැත්තක දිග 6 cm නම්,
   P = 4 × 6 cm = 24 cm

3. ඍජුකෝණාස්‍රයක පරිමිතිය
   P = 2 × (දිග + පළල)
   උදාහරණය: දිග 10 cm සහ පළල 7 cm නම්,
   P = 2 × (10 cm + 7 cm) = 34 cm

4. ත්‍රිකෝණයක පරිමිතිය
   P = පළමු පැත්ත + දෙවන පැත්ත + තෙවන පැත්ත
   උදාහරණය: පැති 5 cm, 6 cm සහ 8 cm නම්,
   P = 5 cm + 6 cm + 8 cm = 19 cm

5. සංයුක්ත සරල රේඛීය රූපයක පරිමිතිය සෙවීමේදී පිටත මායිමේ ඇති සියලු දිග පමණක් එකතු කරන්න. රූපය ඇතුළත ඇති පොදු පැති එකතු නොකරන්න.

6. නොදන්නා පැත්තක් ඇති විට, එකම දිශාවේ ඇති මුළු දිග භාවිත කර එම දිග සොයා ගන්න. ඉන්පසු පිටත පැති සියල්ල එකතු කරන්න.

7. සියලු මිනුම් එකම ඒකකයකට පරිවර්තනය කිරීමෙන් පසුව පමණක් එකතු කරන්න.
   1 m = 100 cm
   1 cm = 10 mm

8. පිළිතුරට නිවැරදි දිග ඒකකය ලියන්න: mm, cm, m හෝ km. පරිමිතිය වර්ග ඒකකවලින් (cm², m²) නොලියයි.

මතක තබා ගන්න
• පරිමිතිය = පිටත මායිමේ මුළු දිග
• සමචතුරස්‍රය: P = 4 × පැත්ත
• ඍජුකෝණාස්‍රය: P = 2 × (දිග + පළල)
• සංයුක්ත රූප: පිටත පැති පමණක් එකතු කරන්න
TEXT;

$sql = "UPDATE lessons l
        INNER JOIN grades g ON g.id=l.grade_id
        INNER JOIN subjects s ON s.id=l.subject_id
        SET l.short_notes_si=?
        WHERE g.grade_number=8
          AND s.subject_code='MATHE8'
          AND l.medium='Sinhala'
          AND l.display_order=2";
$statement=db()->prepare($sql);
$statement->bind_param('s',$notes);
$statement->execute();
if($statement->affected_rows<0)throw new RuntimeException('Could not update the lesson notes.');
echo "Grade 8 Sinhala Mathematics lesson 2 short notes repaired.\n";
