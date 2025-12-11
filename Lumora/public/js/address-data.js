/**
 * public/js/address-data.js
 * Philippine Address Data (Sample)
 * Structure: Region -> Province -> City/Municipality -> [Barangays]
 */

window.addressData = {
    "NCR": {
        "Metro Manila": {
            "Manila": ["Binondo", "Ermita", "Intramuros", "Malate", "Paco", "Pandacan", "Port Area", "Quiapo", "Sampaloc", "San Miguel", "San Nicolas", "Santa Ana", "Santa Cruz", "Santa Mesa", "Tondo"],
            "Quezon City": ["Bagumbayan", "Bahay Toro", "Balingasa", "Commonwealth", "Batasan Hills", "Loyola Heights", "Mariana", "Pinyahan", "Project 6", "San Bartolome", "Tatalon", "Ugong Norte", "UP Campus"],
            "Makati": ["Bangkal", "Bel-Air", "Carmona", "Cembo", "Comembo", "Dasmarinas", "East Rembo", "Forbes Park", "Guadalupe Nuevo", "Guadalupe Viejo", "Kasilawan", "La Paz", "Magallanes"],
            "Taguig": ["Bagumbayan", "Bambang", "Calzada", "Hagonoy", "Ibayo-Tipas", "Ligid-Tipas", "Lower Bicutan", "New Lower Bicutan", "Napindan", "Palingon", "San Miguel", "Santa Ana", "Tuktukan", "Ususan", "Wawa"],
            "Pasig": ["Bagong Ilog", "Bagong Katipunan", "Bambang", "Buting", "Caniogan", "Dela Paz", "Kalawaan", "Kapasigan", "Kapitolyo", "Malinao", "Manggahan", "Maybunga", "Oranbo", "Palatiw", "Pinagbuhatan", "Pineda", "Rosario", "Sagad", "San Antonio", "San Joaquin", "San Jose", "San Miguel", "San Nicolas", "Santa Cruz", "Santa Lucia", "Santa Rosa", "Santo Tomas", "Santolan", "Sumilang", "Ugong"]
        }
    },
    "Region IV-A": {
        "Cavite": {
            "Bacoor": ["Alima", "Aniban I", "Aniban II", "Aniban III", "Aniban IV", "Aniban V", "Bayanan", "Daang Bukid", "Digman", "Dulong Bayan", "Habay I", "Habay II", "Kaingen"],
            "Dasmarinas": ["Burol", "Langkaan I", "Langkaan II", "Paliparan I", "Paliparan II", "Paliparan III", "Sabang", "Salawag", "Salitran I", "Salitran II", "Salitran III", "Salitran IV", "Sampaloc I"],
            "Imus": ["Alapan I-A", "Alapan I-B", "Alapan I-C", "Alapan II-A", "Alapan II-B", "Anabu I-A", "Anabu I-B", "Anabu I-C", "Anabu I-D", "Anabu I-E", "Anabu I-F", "Anabu I-G"],
            "Tagaytay": ["Asisan", "Bagong Tubig", "Calabuso", "Dapdap East", "Dapdap West", "Francisco", "Guinhawa North", "Guinhawa South", "Iruhin East", "Iruhin South", "Iruhin West"]
        },
        "Laguna": {
            "Biñan": ["Biñan", "Bungahan", "Canlalay", "Casile", "De La Paz", "Ganado", "Langkiwa", "Loma", "Malaban", "Malamig", "Mampalasan", "Platero", "Poblacion"],
            "Calamba": ["Bagong Kalsada", "Banadero", "Banlic", "Barandal", "Batino", "Bubuyan", "Bucal", "Bunggo", "Burol", "Camaligan", "Canlubang", "Halang", "Hornalan"],
            "Santa Rosa": ["Aplaya", "Balibago", "Caingin", "Dila", "Dita", "Don Jose", "Ibaba", "Kanluran", "Labas", "Macabling", "Malitlit", "Malusak", "Market Area", "Pooc", "Pulong Santa Cruz", "Santo Domingo", "Sinalhan", "Tagapo"]
        },
        "Batangas": {
            "Batangas City": ["Alangilan", "Balagtas", "Balinsasayaw", "Banaba Center", "Banaba East", "Banaba South", "Banaba West", "Banalo", "Barangay 1", "Barangay 2", "Barangay 3"],
            "Lipa": ["Adya", "Anilao", "Anilao-Labac", "Antipolo Del Norte", "Antipolo Del Sur", "Bagong Pook", "Balintawak", "Banaybanay", "Barangay 1", "Barangay 2", "Barangay 3"]
        }
    },
    "Region III": {
        "Pampanga": {
            "Angeles": ["Agapito del Rosario", "Amsic", "Anunas", "Balibago", "Capaya", "Claro M. Recto", "Cuayan", "Cutcut", "Cutud", "Lourdes North West", "Lourdes Sur", "Lourdes Sur East"],
            "San Fernando": ["Alasas", "Baliti", "Bulaon", "Calulut", "Dela Paz Norte", "Dela Paz Sur", "Del Carmen", "Del Pilar", "Del Rosario", "Dolores", "Juliana", "Lara", "Lourdes"]
        },
        "Bulacan": {
            "Malolos": ["Anilao", "Atlag", "Babatnin", "Bagna", "Bagong Bayan", "Balayong", "Balite", "Bangkal", "Barihan", "Bulihan", "Bungahan", "Caingin", "Calero", "Caliligawan"],
            "Meycauayan": ["Bagbaguin", "Bahay Pare", "Bancal", "Banga", "Bayugo", "Caingin", "Calvario", "Camalig", "Hulo", "Iba", "Langka", "Lawa", "Libtong", "Liputan", "Longos"]
        }
    },
    "Region VII": {
        "Cebu": {
            "Cebu City": ["Adlaon", "Agsungot", "Apas", "Babag", "Bacayan", "Banilad", "Basak Pardo", "Basak San Nicolas", "Binaliw", "Bonbon", "Budlaan", "Buhisan", "Bulacao"],
            "Mandaue": ["Alang-alang", "Bakilid", "Banilad", "Basak", "Cabancalan", "Cambaro", "Canduman", "Casili", "Casuntingan", "Centro", "Cubacub", "Guizo", "Ibabao-Estancia"],
            "Lapu-Lapu": ["Agus", "Babag", "Bankal", "Baring", "Basak", "Buaya", "Calawisan", "Canjulao", "Caw-oy", "Caubian", "Caohagan", "Gun-ob", "Ibo", "Looc", "Mactan"]
        }
    },
    "Region XI": {
        "Davao del Sur": {
            "Davao City": ["Poblacion District", "Talomo District", "Agdao District", "Buhangin District", "Bunawan District", "Paquibato District", "Baguio District", "Calinan District", "Marilog District", "Toril District", "Tugbok District"]
        }
    }
};