<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000'); // Allow requests from your React app
header('Access-Control-Allow-Headers: Content-Type'); // Needed for POST requests
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Example data structure for questions
$questions = [
    [
        "id" => 1,
        "text" => "My Headaches started approximately about {{input}} weeks/ months/ years ago.",
        "type" => "embedded", // Indicating this is a text with input
        "inputType" => "number", // Specify the type of input (text, number, etc.)
        "min" => "0", // Minimum value
        //"defaultValue" => "0" // Default value to ensure the form starts controlled
    ],
    ["id" => 2,
     "text" => "My Headaches started after a head trauma/ concussion /neck trauma / illness/infection?", 
     "type" => "radio", 
     "options" => ["Yes", "No"]
    ],
    ["id" => 3, 
     "text" => "My headache:", 
     "type" => "radio", 
     "options" => ["Usually starts in one side but it can spread to the other side", 
      "Usually starts in both side and entire head", 
      "Always starts in one side and stays at the same side", 
      "Starts on my entire head"],
     "allowOther" => false
    ],
    ["id" => 4, 
     "text" => "Sometime at the beginning of the headache or even before headache starts, I experience", 
     "type" => "radio", 
     "options" => ["Some Visual Changes", 
      "Numbness or tingling in one side of my body", 
      "Difficulty speaking", 
      "Dizziness"]
    ],
    [
     "id" => 5,
     "text" => "Most of my headaches are:",
     "type" => "checkbox",
     "options" => ["Throbbing (pulsating)",
     "Pressure (squeezing)",
     "Dull ache",
     "Band",
     "Sharp (stabbing)", 
     "Exploding",
     "Jabbing (electric shock)",
     "Vice grip sensation"],
     "allowOther" => false
    ],
    [
        "id" => 6,
        "text" => "My headache, particularly when it's severe is associated with:",
        "type" => "checkbox",
        "options" => ["Nausea", 
        "Vomiting", 
        "Stomach discomfort", 
        "Decrease appetite",
        "Diarrhea",
        "Constipation",
        "Sensitivity to light",
        "Sensitivity to noise/sounds",
        "Sensitivity to certain smells", 
        "Yawning", 
        "Feeling dizzy or off", 
        "Excessive thirst",
        "Excessive urination",
        "Feeling restless (pacing myself)",
        "Tearing (watering) of one eye",
        "Droopy eye (s)"]
       ],
       [
        "id" => 7,
        "text" => "My headache can be triggered by:",
        "type" => "checkbox",
        "options" => ["Stress", 
        "Sleep disturbance (too much or too little sleep)", 
        "Missing a meal", 
        "Dehydration",
        "Weather changes",
        "Barometric pressure changes",
        "Flickering or glaring light",
        "Alcohol",
        "Certain smells",
        "Certain food",
        "Menstrual cycle",
        "Other"]
       ],
       [
        "id" => 8,
        "text" => "My headache get worse with:",
        "type" => "checkbox",
        "options" => ["Any kind of exertion and movement, going up or down stairs", 
        "Bending over or lifting objects", 
        "Standing up", 
        "Lying down",
        "Straining/coughing/sneezing",
        "Hot temperature (heat)",
        "Cold temperature"
        ]
       ],
       ["id" => 9, 
     "text" => "My headache usually lasts about", 
     "type" => "radio", 
     "options" => ["Few seconds", 
      "Few minutes", 
      "Between 1-3 hours", 
      "More than 4 hours",
      "More than 3 days"
     ]
    ],
    ["id" => 10, 
     "text" => " I have headache (any type)", 
     "type" => "radio", 
     "options" => ["Less than 4 days/month (1 day/ week)", 
      "Between 4-8 days per month", 
      "Between 8-14 days per month", 
      "More than 15 days per month",
      "Daily"
     ]
    ],
    [
        "id" => 11,
        "text" => "On Average {{input}} days per month, my headache prevents me from doing my routine daily activities..",
        "type" => "embedded", // Indicating this is a text with input
        "inputType" => "number", // Specify the type of input (text, number, etc.)
        "min" => "0", // Minimum value
        //"defaultValue" => "0" // Default value to ensure the form starts controlled
    ],
    [
        "id" => 12,
        "text" => "Intensity of my headache in the scale of 1 to 10, in average is {{input}} and maximum is {{input}}",
        "type" => "embedded", // Indicating this is a text with input
        "inputType" => "number", // Specify the type of input (text, number, etc.)
        "min" => "1", // Minimum value
        //"defaultValue" => "1" // Default value to ensure the form starts controlled
    ],
    [
        "id" => 13,
        "text" => "I have:",
        "type" => "checkbox",
        "options" => ["No problem with my sleep", 
        "Difficulty falling asleep", 
        "Difficulty staying sleep", 
        "Snoring",
        "Awakening at nights due to headache",
        "Nights due to headache Waking up with the headache in the morning",
        ]
    ],
    [
        "id" => 14,
        "text" => "I have a history of:",
        "type" => "checkbox",
        "options" => ["Anxiety", 
        "Depression", 
        "Suicidal ideation", 
        "Bipolar Disorder",
        "ADHD",
        "Constipation",
        "Seasonal allergy",  
        "Motion sickness",
        "Kidney stone",
        "Thyroid disease",
        "Liver disease", 
        "Diarrhea", 
        "Kidney disease",
        "Stomach Ulcer",
        "High blood pressure",
        "Seizure", 
        "Recurrent sinusitis", 
        "Head injury",
        "Neck injury",
        "Concussion",
        "Joint pain",
        "Raynaud’s",
        "Other",
        ]
    ],
    [
        "id" => 15,
        "text" => "Currently what are you taking /using for your headache? (Including over the counter medication)",
        "type" => "textbox", // Indicating this is a text with input
        "inputType" => "text", // Specify the type of input (text, number, etc.)
        //"defaultValue" => "" // Default value to ensure the form starts controlled
    ],
    [
        "id" => 16,
        "text" => "In the past which one of the following abortive medication /treatment have you been used for headache?",
        "type" => "checkbox",
        "options" => ["Ibuprofen (Advil)", 
        "Sumatriptan (IMITREX)", 
        "Rizatriptan (MAXALT)", 
        "Midrin",
        "Naproxen ( Aleve)",
        "Sumatriptan Injection",
        "Frovatriptan (FROVA)",  
        "Naratriptan (AMERGE)",
        "Cafergot",
        "Ketoprofen",
        "Sumatriptan Nose spray", 
        "Eletriptan ( RELPAX)", 
        "Ergomar",
        "Diclofenac Potassium",
        "ONZETRA nose powder",
        "Almotriptan (AXERT)", 
        "DHE injection", 
        "Ketorolac ( Toradol )",
        "ZOMIG nose spray",
        "Zolmitriptan (ZOMIG)",
        "UBRELVY",
        "Acetaminophen",
        "SPRIX nose spray",
        "NURTEC",
        "CAMBIA powder",
        "Migranal nose spray",
        "TREXIMET",
    ],
    "allowOther" => true
    ],
    [
        "id" => 17,
        "text" => "In the past which one of the following preventive medication /treatment have you been used for headache?",
        "type" => "checkbox",
        "options" => ["Propranolol(Inderal)", 
        "Amlodipine", 
        "Topiramate(Topamax)", 
        "AIMOVIG",
        "Metoprolol( Toprol)",
        "Verapamil (Calan)",
        "NERVE BLOCK",  
        "TROKENDI, Qudexy",
        "AJOVY",
        "Flunarizine",
        "Valproic acid(Depakote)", 
        "EMGALITY", 
        "Nadolol",
        "Cinnarizine(Stugeron)",
        "Gabapentin(Neurontin)",
        "VYEPTI",
        "Atenolol",
        "Nortriptyline(Pamelor)",
        "Venlafaxine(Effexor)",
        "Amitriptyline(Elavil)",
        "BOTOX",
        "Candesartan",
        "Feverfew-Petadolex",
        "Timolol"
    ],
    "allowOther" => true
    ]
];
echo json_encode(["questions" => $questions]);
?>