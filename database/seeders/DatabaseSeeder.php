<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Setting;
use App\Models\Blog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admin User ───
        User::firstOrCreate(['email' => 'admin@citydrug.pk'], [
            'name'              => 'City Drug Admin',
            'password'          => bcrypt('admin123'),
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // Demo pharmacist (prescriptions only). Change or remove before going live.
        User::firstOrCreate(['email' => 'pharmacist@citydrug.pk'], [
            'name'              => 'Demo Pharmacist',
            'password'          => bcrypt('pharmacist123'),
            'role'              => 'pharmacist',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        User::firstOrCreate(['email' => 'customer@example.com'], [
            'name'              => 'Ahmad Raza',
            'password'          => bcrypt('password'),
            'phone'             => '+92 301 1234567',
            'city'              => 'Multan',
            'role'              => 'customer',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // ─── Categories ───
        $categoryData = [
            ['Medicines',          'medicines',         '💊', '#0d6efd', 'Prescription & OTC medicines, tablets, capsules, syrups and injections from top pharmaceutical brands.'],
            ['Vitamins & Supplements', 'supplements',   '🌿', '#39cb74', 'Vitamins, minerals, antioxidants and dietary supplements for optimal health and wellness.'],
            ['Personal Care',      'personal-care',     '🧴', '#f2971f', 'Skincare, haircare, oral hygiene and personal hygiene products for daily use.'],
            ['Baby & Kids',        'baby-kids',         '👶', '#e84393', 'Baby food, diapers, baby skincare, medicines and developmental products for infants and children.'],
            ['Sports Nutrition',   'sports-nutrition',  '💪', '#8b5cf6', 'Protein powders, creatine, pre-workout, BCAA and performance nutrition for athletes.'],
            ['Herbs & Naturals',   'herbs-naturals',    '🌱', '#059669', 'Herbal remedies, natural supplements, Unani and homeopathic products.'],
            ['Medical Devices',    'medical-devices',   '🩺', '#dc2626', 'Blood pressure monitors, glucometers, thermometers, oximeters and medical equipment.'],
            ['Skin Care',          'skin-care',         '✨', '#ec4899', 'Moisturizers, serums, sunscreens, face washes, anti-aging creams and skin treatments.'],
            ['Eye Care',           'eye-care',          '👁️', '#06b6d4', 'Eye drops, contact lens solutions, eye vitamins and ophthalmic products.'],
            ['Dental Care',        'dental-care',       '🦷', '#0ea5e9', 'Toothpaste, mouthwash, dental floss, toothbrushes and oral hygiene products.'],
            ['Digestive Health',   'digestive-health',  '🫁', '#84cc16', 'Antacids, probiotics, digestive enzymes, fiber supplements and gut health products.'],
            ['Diabetic Care',      'diabetic-care',     '🩸', '#f59e0b', 'Insulin, blood glucose strips, diabetic foods, supplements and monitoring devices.'],
        ];

        $catModels = [];
        foreach ($categoryData as $i => [$name, $slug, $icon, $color, $desc]) {
            $catModels[$slug] = Category::firstOrCreate(['slug' => $slug], [
                'name'        => $name,
                'description' => $desc,
                'icon'        => $icon,
                'color'       => $color,
                'is_active'   => true,
                'sort_order'  => $i,
            ]);
        }

        // ─── Products ───
        // [name, category_slug, price, sale_price|null, stock, is_featured, is_new, requires_prescription, brand, dosage, composition, description]
        $products = [
            // ── MEDICINES ──
            ['Panadol Extra Tabs x30',         'medicines',    250,  null, 150, true,  true,  false, 'GSK',         '500mg', 'Paracetamol 500mg + Caffeine 65mg', 'Fast pain relief for headache, fever, body aches and dental pain.'],
            ['Disprin Soluble Tabs x10',        'medicines',     80,  null, 200, false, false, false, 'Reckitt',     '300mg', 'Aspirin 300mg', 'Soluble aspirin tablets for pain, fever and inflammation relief.'],
            ['Augmentin 625mg Tabs x14',        'medicines',    850,  null,  80, true,  false, true,  'GSK',         '625mg', 'Amoxicillin 500mg + Clavulanate 125mg', 'Broad-spectrum antibiotic for bacterial infections.'],
            ['Brufen 400mg Tabs x20',           'medicines',    180,  null, 120, false, true,  false, 'Abbott',      '400mg', 'Ibuprofen 400mg', 'Anti-inflammatory for pain, fever and arthritis.'],
            ['Amoxil 250mg Capsules x12',       'medicines',    320,  null,  90, false, false, true,  'GSK',         '250mg', 'Amoxicillin 250mg', 'Penicillin antibiotic for respiratory, ear and skin infections.'],
            ['Flagyl 400mg Tabs x14',           'medicines',    280,  null,  85, false, false, true,  'Sanofi',      '400mg', 'Metronidazole 400mg', 'Antiprotozoal and antibacterial for gut and vaginal infections.'],
            ['Ciproxin 500mg Tabs x10',         'medicines',    750,  null,  60, false, false, true,  'Bayer',       '500mg', 'Ciprofloxacin 500mg', 'Fluoroquinolone antibiotic for UTI, chest and skin infections.'],
            ['Nexium 20mg Tabs x14',            'medicines',    680, 599,   70, true,  false, true,  'AstraZeneca', '20mg',  'Esomeprazole 20mg', 'Proton pump inhibitor for acid reflux, GERD and peptic ulcers.'],
            ['Ventolin Inhaler 100mcg',         'medicines',    650,  null,  45, false, false, true,  'GSK',         '100mcg','Salbutamol 100mcg/puff', 'Bronchodilator inhaler for asthma and COPD relief.'],
            ['Lopressor 50mg Tabs x28',         'medicines',    420,  null,  55, false, false, true,  'Novartis',    '50mg',  'Metoprolol 50mg', 'Beta-blocker for hypertension and heart conditions.'],
            ['Glucophage 500mg Tabs x30',       'medicines',    380,  null,  65, true,  false, true,  'Merck',       '500mg', 'Metformin 500mg', 'First-line medication for type 2 diabetes management.'],
            ['Voltaren Gel 50g',                'medicines',    580, 499,   80, true,  true,  false, 'Novartis',    '1%',    'Diclofenac Sodium 1%', 'Topical anti-inflammatory gel for joint and muscle pain.'],
            ['ORS Sachet Apple Flavor x10',     'medicines',    120,  null, 300, false, false, false, 'Abbott',      null,    'Sodium Chloride, Potassium Chloride, Glucose', 'Oral rehydration salts for diarrhea and dehydration.'],
            ['Clarinase 12hr Tabs x10',         'medicines',    480,  null,  70, false, false, false, 'MSD',         '5mg/120mg', 'Loratadine 5mg + Pseudoephedrine 120mg', 'Non-drowsy antihistamine for allergy, cold and hay fever.'],
            ['Ponstan 500mg Caps x24',          'medicines',    380,  null, 100, false, false, false, 'Pfizer',      '500mg', 'Mefenamic Acid 500mg', 'Pain reliever for period pain, toothache and headache.'],
            ['Duoneb Inhaler 2.5mg',            'medicines',    890,  null,  30, false, false, true,  'Novartis',    '2.5mg', 'Ipratropium + Albuterol', 'Combination bronchodilator for COPD management.'],
            ['Prednisolone 5mg Tabs x20',       'medicines',    190,  null, 120, false, false, true,  'Sanofi',      '5mg',   'Prednisolone 5mg', 'Corticosteroid for inflammation, allergies and immune conditions.'],
            ['Zithromax 250mg Caps x6',         'medicines',    680,  null,  50, false, false, true,  'Pfizer',      '250mg', 'Azithromycin 250mg', 'Macrolide antibiotic for respiratory and skin infections.'],

            // ── VITAMINS & SUPPLEMENTS ──
            ['Vitamin C 1000mg Tabs x30',       'supplements',  450, 379,  200, true,  true,  false, 'Centrum',    '1000mg', 'Ascorbic Acid 1000mg', 'High-dose Vitamin C for immunity, skin health and antioxidant protection.'],
            ['Vitamin D3 5000IU Caps x60',      'supplements',  750, 649,  150, true,  false, false, 'NaturePlus', '5000IU', 'Cholecalciferol 5000IU', 'High-strength Vitamin D3 for bone health, immunity and mood support.'],
            ['Omega-3 Fish Oil 1000mg x60',     'supplements',  950,  null,  90, false, true,  false, 'Dr. Morepen','1000mg', 'EPA 180mg + DHA 120mg', 'Premium omega-3 for heart health, brain function and joint support.'],
            ['Multivitamin Complete x30',        'supplements',  580, 499,  180, true,  true,  false, 'Centrum',    null,    '26 Essential Vitamins & Minerals', 'Complete daily multivitamin for men and women energy and immunity.'],
            ['Calcium + Vitamin D3 x60',        'supplements',  650,  null, 140, false, false, false, 'Osteocare',  '600mg', 'Calcium 600mg + D3 400IU + Magnesium', 'Bone health formula for strong bones and teeth.'],
            ['Vitamin B Complex x100',           'supplements',  380, 320,  200, false, false, false, 'Becadex',    null,    'B1, B2, B3, B5, B6, B7, B9, B12', 'Complete B-vitamin complex for energy, nerves and metabolism.'],
            ['Iron + Folic Acid x30',           'supplements',  280,  null, 180, false, false, false, 'Abbott',     '65mg',  'Ferrous Sulfate 65mg + Folic Acid 0.4mg', 'Iron supplement for anemia prevention and pregnancy.'],
            ['Zinc Picolinate 50mg x60',        'supplements',  420,  null, 130, false, true,  false, 'Now Foods',  '50mg',  'Zinc Picolinate 50mg', 'Bioavailable zinc for immunity, wound healing and skin health.'],
            ['Magnesium Glycinate 400mg x60',   'supplements',  680,  null,  80, false, false, false, 'Vitabiotics', '400mg','Magnesium Glycinate 400mg', 'Gentle magnesium for muscle relaxation, sleep and stress relief.'],
            ['CoQ10 100mg Caps x30',            'supplements',  980, 849,   60, true,  false, false, 'Solgar',     '100mg', 'Coenzyme Q10 100mg', 'Powerful antioxidant for heart health and cellular energy production.'],
            ['Biotin 10000mcg x60',             'supplements',  550, 469,  100, true,  true,  false, 'NaturePlus', '10000mcg','Biotin (Vitamin B7) 10000mcg', 'High-strength biotin for hair growth, strong nails and healthy skin.'],
            ['Glucosamine + Chondroitin x60',   'supplements',  880,  null,  70, false, false, false, 'Osteocare',  '500mg', 'Glucosamine 500mg + Chondroitin 400mg', 'Joint support formula for arthritis, mobility and cartilage health.'],

            // ── PERSONAL CARE ──
            ['Dettol Antiseptic Liquid 100ml',  'personal-care', 280,  null, 300, false, false, false, 'Reckitt',    null, 'Chloroxylenol 4.8%', 'Multi-purpose antiseptic for wound care, bathing and household hygiene.'],
            ['Nivea Body Lotion 400ml',          'personal-care', 850, 699,  150, true,  false, false, 'Nivea',      null, 'Glycerin, Pro-Vitamin B5', 'Deep moisturizing body lotion for smooth, hydrated skin for 48 hours.'],
            ['Lifebuoy Total Hand Wash 200ml',  'personal-care', 180,  null, 400, false, false, false, 'Unilever',   null, 'Activ Silver Formula', 'Anti-bacterial hand wash with silver ions for 99.9% germ protection.'],
            ['Sensodyne Toothpaste 70g',         'personal-care', 420, 369,  200, false, true,  false, 'GSK',        null, 'Potassium Nitrate 5% + Fluoride', 'Sensitive teeth toothpaste for fast and lasting pain relief.'],
            ['Head & Shoulders Shampoo 185ml',  'personal-care', 560, 489,  180, false, false, false, 'P&G',        null, 'Pyrithione Zinc 1%', 'Anti-dandruff shampoo for clean, flake-free scalp and hair.'],
            ['Vaseline Petroleum Jelly 250ml',  'personal-care', 380,  null, 250, false, false, false, 'Unilever',   null, 'White Petrolatum 100%', 'Pure petroleum jelly for dry skin, lip care and minor wound healing.'],
            ['Johnson\'s Baby Powder 200g',      'personal-care', 280,  null, 300, false, false, false, 'J&J',        null, 'Talc, Fragrance', 'Gentle talc powder to keep skin dry and comfortable all day.'],
            ['Colgate Total Toothpaste 75g',    'personal-care', 320, 280,  300, false, false, false, 'Colgate',    null, 'Sodium Fluoride 0.32%', '12-hour antibacterial protection for complete oral health care.'],

            // ── BABY & KIDS ──
            ['Pampers Active Baby NB x30',       'baby-kids',    950, 849,   80, true,  true,  false, 'P&G',        null, null, 'Newborn diapers with Extra Dry System for up to 12 hours protection.'],
            ['Johnson\'s Baby Shampoo 200ml',    'baby-kids',    380,  null, 150, false, false, false, 'J&J',        null, 'Tear-Free Formula', 'No More Tears formula - as gentle to eyes as pure water.'],
            ['Calpol Infant Syrup 60ml',         'baby-kids',    220,  null, 200, true,  false, false, 'J&J',        '120mg/5ml', 'Paracetamol 120mg/5ml', 'Sugar-free paracetamol syrup for infant pain and fever relief.'],
            ['Pediasure Complete 400g Vanilla',  'baby-kids',   2800,2499,   40, true,  false, false, 'Abbott',     null, 'Balanced nutrition with DHA, Iron, Vitamins', 'Complete nutrition shake for picky eaters and catch-up growth.'],
            ['Cerelac Stage 1 Rice 175g',        'baby-kids',    680, 599,   90, false, false, false, 'Nestle',     null, 'Rice Flour, Milk Powder, Iron, Vitamins', 'Iron-fortified infant cereal for 6+ months babies.'],
            ['Dentinox Teething Gel 10g',        'baby-kids',    280,  null, 120, false, false, false, 'Crawford',   null, 'Lidocaine 0.33% + Cetylpyridinium', 'Soothing teething gel for baby gum pain relief.'],
            ['Gripe Water 150ml',                 'baby-kids',    180,  null, 250, false, false, false, 'Woodward\'s', null, 'Sodium Bicarbonate + Dill Seed Oil', 'Traditional remedy for infant colic, gas and stomach discomfort.'],
            ['Vitamin D Drops 400IU 10ml',       'baby-kids',    380,  null, 150, false, true,  false, 'D-Vi-Sol',   '400IU', 'Cholecalciferol 400IU/mL', 'Essential Vitamin D drops for newborns and infants from birth.'],

            // ── SPORTS NUTRITION ──
            ['ON Gold Whey Protein 1lb Choc',   'sports-nutrition',3800,3299, 30, true,  true,  false, 'Optimum Nutrition','24g/serving','Whey Protein Concentrate & Isolate', 'World\'s best-selling whey protein for muscle recovery and growth.'],
            ['Creatine Monohydrate 250g',        'sports-nutrition',1800, null, 60, false, false, false, 'MyProtein',  '5g/serving',  'Creatine Monohydrate 100%', 'Pure creatine monohydrate for strength, power and muscle gains.'],
            ['BCAA 2:1:1 200g Watermelon',      'sports-nutrition',2200,1899,  45, false, true,  false, 'MyProtein',  '10g/serving', 'Leucine 5g + Isoleucine 2.5g + Valine 2.5g', 'Essential amino acids to prevent muscle breakdown and aid recovery.'],
            ['Pre-Workout C4 60 servings',       'sports-nutrition',4500,3999,  25, true,  false, false, 'Cellucor',   '6g/serving',  'Beta-Alanine, Creatine, Caffeine 150mg', 'Explosive pre-workout for energy, focus and performance.'],
            ['Mass Gainer 3kg Chocolate',        'sports-nutrition',5500,4999,  20, true,  false, false, 'Serious Mass', '50g protein/serving', 'Blend of Proteins, Carbs & Fats', 'High-calorie mass gainer for hard gainers and bulking phase.'],
            ['L-Carnitine 3000mg 30 servings',  'sports-nutrition',2800,2399,  35, false, true,  false, 'NOW Sports',  '3000mg/serving','L-Carnitine L-Tartrate 3000mg', 'Supports fat burning, energy production and exercise recovery.'],
            ['Protein Bar 60g Peanut Butter',   'sports-nutrition', 350, 299,  100, false, true, false,  'Clif Bar',   '21g protein', 'Whey Protein, Oats, Peanut Butter', 'High-protein snack bar for on-the-go nutrition.'],

            // ── HERBS & NATURALS ──
            ['Moringa Leaf Capsules x60',        'herbs-naturals', 650, 549, 100, true,  true,  false, 'Himalaya',   '500mg', 'Moringa Oleifera Leaf Extract 500mg', 'Superfood rich in vitamins, minerals and antioxidants.'],
            ['Ashwagandha Root Caps x60',        'herbs-naturals', 780, 679, 80,  true,  false, false, 'Himalaya',   '300mg', 'Ashwagandha Root Extract 300mg', 'Adaptogenic herb for stress relief, energy and hormone balance.'],
            ['Turmeric Curcumin 500mg x60',      'herbs-naturals', 720, 629, 90,  false, true,  false, 'Himalaya',   '500mg', 'Curcumin 500mg + Piperine 5mg', 'Anti-inflammatory turmeric with black pepper for better absorption.'],
            ['Triphala Churna 100g',              'herbs-naturals', 280,  null,150, false, false, false, 'Hamdard',    '5g/serving', 'Amalaki, Bibhitaki, Haritaki', 'Traditional Ayurvedic blend for digestion and detoxification.'],
            ['Ajwain Herbal Tea x20 bags',       'herbs-naturals', 180,  null,200, false, false, false, 'Hamdard',    null, 'Trachyspermum ammi', 'Traditional herbal tea for digestive relief and bloating.'],
            ['Ginger Lemon Honey Syrup 200ml',   'herbs-naturals', 350,  null,120, false, false, false, 'Qarshi',     null, 'Ginger Extract, Lemon, Raw Honey', 'Natural immunity booster and sore throat relief syrup.'],
            ['Neem Capsules x60',                 'herbs-naturals', 480, 399,  80, false, false, false, 'Himalaya',   '500mg', 'Neem Leaf Extract 500mg', 'Purifying herb for skin health, blood purification and immunity.'],

            // ── MEDICAL DEVICES ──
            ['Omron BP Monitor HEM-7120',        'medical-devices',5500,4999,  25, true,  true,  false, 'Omron',      null, null, 'Digital upper arm blood pressure monitor with memory for 60 readings.'],
            ['Accu-Chek Active Glucometer',      'medical-devices',3800,3299,  30, true,  false, false, 'Roche',      null, null, 'Blood glucose monitoring system with 50 test strips included.'],
            ['Oximeter Pulse Fingertip',          'medical-devices',1800,1499,  50, true,  true,  false, 'ChoiceMMed', null, null, 'Fingertip pulse oximeter for SpO2 and heart rate monitoring.'],
            ['Digital Thermometer Oral',          'medical-devices', 450, 379, 150, false, false, false, 'Omron',      null, null, '10-second fast reading digital thermometer with fever alarm.'],
            ['Peak Flow Meter Adult',             'medical-devices', 980,  null,  40, false, false, false, 'Romsons',   null, null, 'Peak expiratory flow meter for asthma monitoring and control.'],
            ['Nebulizer Compressor CN-116',       'medical-devices',4200,3699,  20, false, false, false, 'Beurer',     null, null, 'Compact compressor nebulizer for asthma, bronchitis and COPD.'],
            ['Wrist BP Monitor Automatic',        'medical-devices',3500,2999,  35, false, true,  false, 'Beurer',     null, null, 'Easy-to-use wrist blood pressure monitor with irregular heartbeat detection.'],

            // ── SKIN CARE ──
            ['Neutrogena Hydro Boost Gel 50ml',  'skin-care',    1800,1599,  60, true,  true,  false, 'Neutrogena',  null, 'Hyaluronic Acid', '2% Hyaluronic Acid water gel moisturizer for dehydrated skin.'],
            ['CeraVe Moisturizing Cream 340g',   'skin-care',    2200,1899,  45, true,  false, false, 'CeraVe',      null, 'Ceramides, Hyaluronic Acid, Niacinamide', 'Dermatologist-developed cream for dry to very dry skin.'],
            ['SPF 50 Sunscreen 75ml',             'skin-care',     850, 729,  80, false, true,  false, 'Neutrogena',  'SPF50', 'Octinoxate, Zinc Oxide', 'Broad spectrum PA++++ sunscreen for daily UV protection.'],
            ['Vitamin C Serum 30ml',              'skin-care',    1500,1299,  55, true,  true,  false, 'Ordinary',    '20%',   'Vitamin C (Ascorbic Acid) 20%', 'Brightening vitamin C serum for dark spots and glowing skin.'],
            ['Retinol 0.5% Night Cream 50ml',    'skin-care',    1800,1599,  40, false, false, false, 'RoC',         '0.5%',  'Retinol 0.5%, Vitamin E', 'Anti-aging retinol night cream for fine lines and wrinkles.'],
            ['Kojic Acid Soap 80g',               'skin-care',     280, 249, 200, false, true,  false, 'Lumiair',     '2%',    'Kojic Acid 2% + Glutathione', 'Skin lightening soap for dark spots, hyperpigmentation and even tone.'],

            // ── EYE CARE ──
            ['Rohto Eye Drops Cooling 13ml',     'eye-care',      380, 329,  120, true, true,   false, 'Rohto',       null, 'Tetrahydrozoline HCl 0.05%', 'Cooling eye drops for red, itchy and tired eyes.'],
            ['Systane Ultra Eye Drops 10ml',     'eye-care',      580,  null,  80, false, false, false, 'Alcon',       null, 'Polyethylene Glycol, Propylene Glycol', 'Lubricating eye drops for dry eye symptoms and contact lens comfort.'],
            ['Vizulize Eye Wash 200ml',           'eye-care',      350,  null, 100, false, false, false, 'Vizulize',    null, 'Sodium Chloride Isotonic Solution', 'Sterile eye wash for cleansing and refreshing tired eyes.'],
            ['Lutein 20mg + Zeaxanthin Caps x30','eye-care',      780, 699,   60, true, false,  false, 'Solgar',      '20mg','Lutein 20mg + Zeaxanthin 4mg', 'Macular health supplement for age-related eye protection.'],

            // ── DENTAL CARE ──
            ['Colgate Plax Mouthwash 500ml',     'dental-care',   580, 499,  150, false, true,  false, 'Colgate',     null, 'CPC 0.05%, Sodium Fluoride 0.025%', '12-hour antibacterial mouthwash for complete oral protection.'],
            ['Oral-B Pro-Flex Medium Brush',     'dental-care',   380, 329,  200, false, false, false, 'Oral-B',      null, 'Criss-Cross Bristles', 'Pro-Flex toothbrush with flexing sides to clean hard-to-reach areas.'],
            ['Parodontax Toothpaste 75ml',       'dental-care',   520, 459,  120, false, false, false, 'GSK',         null, 'Sodium Bicarbonate + Herbal Extracts', 'Anti-bleeding gum toothpaste for healthy gums and teeth.'],
            ['Listerine Cool Mint 500ml',        'dental-care',   650, 579,  130, false, true,  false, 'J&J',         null, 'Thymol, Eucalyptol, Menthol, Methyl Salicylate', 'Essential oil mouthwash to kill 99.9% of germs.'],

            // ── DIGESTIVE HEALTH ──
            ['Gaviscon Liquid 300ml',            'digestive-health', 780, 699,  90, true, true,  false, 'Reckitt',     null, 'Sodium Alginate 500mg + Sodium Bicarbonate', 'Fast-acting heartburn and acid reflux relief liquid.'],
            ['Nexium 40mg Tabs x14',             'digestive-health', 950,  null, 55, false, false, true,  'AstraZeneca','40mg', 'Esomeprazole 40mg', 'Powerful PPI for severe GERD, ulcers and esophagitis.'],
            ['Probiotic 10 Billion CFU x30',     'digestive-health', 880, 779,  70, true, true,  false, 'Culturelle',  '10B CFU','Lactobacillus rhamnosus GG', 'High-potency probiotic for gut health, bloating and immunity.'],
            ['Lactulose Syrup 200ml',             'digestive-health', 380,  null,100, false, false, false, 'Sanofi',    '10g/15ml', 'Lactulose 67% w/v', 'Osmotic laxative for constipation relief in adults and children.'],
            ['Buscopan 10mg Tabs x20',           'digestive-health', 320,  null, 80, false, false, false, 'Sanofi',    '10mg', 'Hyoscine-N-butylbromide 10mg', 'Antispasmodic for stomach cramps, IBS and period pain.'],
            ['Digestive Enzyme Complex x60',     'digestive-health', 780, 679,  60, false, true,  false, 'NOW Foods',  null, 'Amylase, Protease, Lipase, Cellulase', 'Comprehensive enzyme blend to optimize food digestion.'],

            // ── DIABETIC CARE ──
            ['Accu-Chek Test Strips x25',        'diabetic-care',  1800,1599,  40, true, true,   false, 'Roche',       null, null, 'Blood glucose test strips compatible with Accu-Chek Active and Performa.'],
            ['Glucerna Shake Vanilla 400g',      'diabetic-care',  3200,2799,  25, true, false,  false, 'Abbott',      null, 'Slow-release carbohydrates + Chromium', 'Diabetic nutrition shake with CARBSTEADY for steady blood sugar.'],
            ['Metformin 1000mg Tabs x30',        'diabetic-care',   550,  null, 60, false, false, true,  'Merck',      '1000mg', 'Metformin HCl 1000mg', 'Extended-release metformin for better blood sugar control.'],
            ['Alpha Lipoic Acid 600mg x30',      'diabetic-care',   880, 779,  50, false, true,  false, 'Solgar',     '600mg', 'Alpha Lipoic Acid 600mg', 'Powerful antioxidant for diabetic neuropathy and blood sugar support.'],
        ];

        $count = 0;
        foreach ($products as $p) {
            [$name, $catSlug, $price, $salePrice, $stock, $isFeatured, $isNew, $rx, $brand, $dosage, $composition, $description] = $p;

            if (!isset($catModels[$catSlug])) continue;

            $slug = Str::slug($name) . '-' . substr(md5($name), 0, 4);
            Product::firstOrCreate(['slug' => $slug], [
                'category_id'            => $catModels[$catSlug]->id,
                'name'                   => $name,
                'slug'                   => $slug,
                'sku'                    => 'CD-' . strtoupper(substr($catSlug, 0, 3)) . '-' . str_pad(++$count, 4, '0', STR_PAD_LEFT),
                'brand'                  => $brand,
                'description'            => $description,
                'price'                  => $price,
                'sale_price'             => $salePrice,
                'stock'                  => $stock,
                'dosage'                 => $dosage,
                'composition'            => $composition,
                'is_active'              => true,
                'is_featured'            => $isFeatured,
                'is_new'                 => $isNew,
                'is_popular'             => rand(0, 1),
                'is_sale'                => $salePrice !== null,
                'requires_prescription'  => $rx,
                'rating'                 => round(rand(35, 50) / 10, 1),
                'reviews_count'          => rand(5, 120),
                'expiry_date'            => now()->addMonths(rand(6, 36)),
                'sort_order'             => $count,
            ]);
        }

        // ─── Settings ───
        $settings = [
            ['general', 'site_name',            'CityDrug'],
            ['general', 'site_tagline',          'Medicines, vitamins and everyday healthcare, with prescription medicines released only after pharmacist verification.'],
            ['general', 'site_email',            ''],
            ['general', 'site_phone',            ''],
            ['general', 'site_address',          ''],
            ['general', 'site_city',             'Multan'],
            ['general', 'whatsapp',              '923001234567'],   // placeholder: set your real number in Admin > Store settings
            ['general', 'free_shipping_min',     '999'],
            ['general', 'currency',              'Rs.'],
            ['appearance', 'primary_color',      '#0d6efd'],
            ['appearance', 'accent_color',       '#f2971f'],
            ['appearance', 'announcement_text',  'Free delivery on orders over Rs. 999'],
            ['appearance', 'show_announcement',  '1'],
        ];
        foreach ($settings as [$group, $key, $value]) {
            Setting::firstOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
        }

        // ─── Sample Blogs ───
        $admin = User::where('role', 'admin')->first();
        $blogPosts = [
            [
                'title'            => 'How to Choose the Right Vitamin D Supplement in Pakistan',
                'category'         => 'Nutrition',
                'excerpt'          => 'Vitamin D deficiency affects over 80% of Pakistanis. Learn how to choose the right supplement dosage for your needs.',
                'content'          => '<h2>Why Vitamin D is Crucial for Pakistanis</h2><p>Despite abundant sunshine, Vitamin D deficiency is alarmingly common in Pakistan, affecting over 80% of the population. This paradox occurs due to indoor lifestyles, conservative clothing, and darker skin pigmentation that reduces UV absorption.</p><h2>Signs of Vitamin D Deficiency</h2><p>Common symptoms include bone pain, muscle weakness, fatigue, depression, and frequent infections. A blood test (25-OH Vitamin D) can confirm deficiency.</p><h2>Recommended Dosages</h2><p>For most deficient adults, doctors recommend <strong>50,000 IU weekly</strong> for 8-12 weeks as loading dose, then 1,000-2,000 IU daily for maintenance. Children need 400-1,000 IU daily.</p><h2>Best Forms of Vitamin D</h2><p>Always choose <strong>Vitamin D3 (Cholecalciferol)</strong> over D2, as it raises blood levels 87% more effectively. Look for oil-based capsules or soft gels for better absorption.</p><h2>Our Recommendation</h2><p>At CityDrug, we stock high-quality Vitamin D3 supplements from trusted brands. Consult our pharmacist for personalized advice based on your blood test results.</p>',
                'tags'             => ['vitamin d', 'supplements', 'deficiency', 'pakistan', 'health'],
                'meta_description' => 'Learn how to choose the right Vitamin D supplement for Pakistani adults. Dosage guide, best forms and expert pharmacist advice from CityDrug.',
                'is_featured'      => true,
            ],
            [
                'title'            => 'Top 10 Medicines Every Pakistani Home Should Have',
                'category'         => 'Medicines',
                'excerpt'          => 'Be prepared for common health emergencies with this essential home medicine kit guide from our expert pharmacists.',
                'content'          => '<h2>Build Your Home Medicine Kit</h2><p>Having a well-stocked medicine cabinet can help you handle minor health issues quickly and safely. Here are the 10 essential medicines every Pakistani household should keep.</p><h2>1. Panadol (Paracetamol)</h2><p>The most essential medicine — safe for all ages for fever, headache and mild to moderate pain. Always have the adult and children\'s versions.</p><h2>2. ORS (Oral Rehydration Salts)</h2><p>Critical for Pakistan\'s hot climate. Use for diarrhea, vomiting and heat exhaustion to prevent dangerous dehydration.</p><h2>3. Gaviscon or Antacid</h2><p>For heartburn and indigestion after spicy Pakistani food. Keep liquid form for faster relief.</p><h2>4. Antihistamine (Clarityne/Zyrtec)</h2><p>For allergy symptoms, hay fever and dust allergy — very common in Multan and Punjab.</p><h2>5. Antiseptic (Dettol/Savlon)</h2><p>For cleaning wounds, cuts and scrapes. Essential in households with children.</p><h2>Important Warning</h2><p>Always check expiry dates regularly and store medicines away from humidity and heat. Keep medicines out of reach of children.</p>',
                'tags'             => ['home medicine', 'first aid', 'essential medicines', 'health kit'],
                'meta_description' => 'Essential medicines every Pakistani home should stock for emergencies. Expert guide from CityDrug pharmacists on building your home medicine cabinet.',
                'is_featured'      => true,
            ],
            [
                'title'            => 'Protein Supplements Guide for Pakistani Athletes',
                'category'         => 'Fitness',
                'excerpt'          => 'Everything you need to know about choosing, using and buying protein supplements in Pakistan for maximum muscle gains.',
                'content'          => '<h2>Why Protein Supplements?</h2><p>Whole foods are always the priority, but Pakistani diets often fall short of protein needs for serious athletes. Supplements bridge the gap conveniently and cost-effectively.</p><h2>Types of Protein</h2><p><strong>Whey Protein Concentrate (WPC):</strong> 70-80% protein, affordable, best for most people. <strong>Whey Isolate (WPI):</strong> 90%+ protein, low fat and lactose, ideal for lactose-intolerant athletes. <strong>Casein:</strong> Slow-release protein, best taken before bed.</p><h2>How Much Do You Need?</h2><p>Research recommends 1.6-2.2g of protein per kg of body weight for muscle building. A 70kg athlete needs 112-154g daily. If you get 90g from food, one 25g protein shake fills the gap.</p><h2>Best Time to Take</h2><p>Post-workout within 30-60 minutes is optimal. Morning or anytime you need to hit your daily protein target also works well.</p><h2>Authenticity Warning</h2><p>Fake supplements are rampant in Pakistan. Always buy from authorized dealers like CityDrug to ensure product authenticity and safety.</p>',
                'tags'             => ['protein powder', 'whey protein', 'muscle building', 'sports nutrition', 'athletes'],
                'meta_description' => 'Complete guide to protein supplements for Pakistani athletes. Types, dosage, timing and where to buy genuine protein powder in Pakistan.',
                'is_featured'      => false,
            ],
            [
                'title'            => 'Managing Diabetes Naturally: A Pakistani Perspective',
                'category'         => 'Health Tips',
                'excerpt'          => 'Practical strategies combining traditional Pakistani wisdom with modern medicine for better blood sugar control.',
                'content'          => '<h2>Pakistan\'s Diabetes Crisis</h2><p>Pakistan has the third-highest number of diabetic patients in the world, with over 33 million affected. Yet 50% remain undiagnosed. Understanding this disease is critical for every Pakistani family.</p><h2>Traditional Pakistani Foods That Help</h2><p>Many traditional Pakistani foods have blood sugar benefits: <strong>Karela (bitter gourd)</strong> contains charantin which acts like insulin. <strong>Methi (fenugreek)</strong> seeds slow glucose absorption. <strong>Turmeric</strong> improves insulin sensitivity. <strong>Cinnamon</strong> mimics insulin action.</p><h2>Foods to Limit</h2><p>White rice (opt for brown), white bread (choose whole wheat roti), sweet chai (reduce sugar gradually), packaged biscuits and fried snacks.</p><h2>Exercise for Diabetics</h2><p>30 minutes of brisk walking daily can reduce HbA1c by 0.5-1%. Aim for at least 150 minutes of moderate exercise weekly. Check blood sugar before and after exercise.</p><h2>Monitoring is Key</h2><p>Check blood sugar 2 hours after meals. Target: below 140 mg/dL. Keep an Accu-Chek glucometer at home for regular monitoring.</p>',
                'tags'             => ['diabetes', 'blood sugar', 'pakistan', 'healthy eating', 'glucometer'],
                'meta_description' => 'Practical guide to managing diabetes naturally in Pakistan. Traditional foods, lifestyle changes and monitoring tips from CityDrug pharmacists.',
                'is_featured'      => false,
            ],
        ];

        foreach ($blogPosts as $i => $post) {
            $slug = Str::slug($post['title']) . '-' . substr(md5($post['title']), 0, 4);
            $words = str_word_count(strip_tags($post['content']));
            Blog::firstOrCreate(['slug' => $slug], [
                'title'            => $post['title'],
                'slug'             => $slug,
                'excerpt'          => $post['excerpt'],
                'content'          => $post['content'],
                'category'         => $post['category'],
                'tags'             => $post['tags'],
                'author_id'        => $admin?->id,
                'meta_description' => $post['meta_description'],
                'meta_title'       => $post['title'] . ' | CityDrug Pakistan',
                'meta_keywords'    => implode(', ', $post['tags']) . ', citydrug, pharmacy pakistan',
                'is_published'     => true,
                'is_featured'      => $post['is_featured'],
                'views'            => rand(150, 2500),
                'reading_time'     => max(1, ceil($words / 200)),
                'published_at'     => now()->subDays($i * 7),
            ]);
        }
    }
}
