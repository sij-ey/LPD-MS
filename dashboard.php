<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: index.html");
    exit();
}
include 'db_connect.php';

// Determine county: from session or database
if (isset($_SESSION['county'])) {
    $county = $_SESSION['county'];
} else {
    // Fallback: get county from database
    $farmer_name = $_SESSION['name'];
    $sql = "SELECT county FROM farmers WHERE name = '$farmer_name' LIMIT 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $county = $row['county'];
    $_SESSION['county'] = $county; // Save for future use
}

// Fetch animal history for dashboard
$result = $conn->query("SELECT * FROM animal_history ORDER BY date_reported DESC LIMIT 6");

// Find vet in the same county
$vet_sql = "SELECT name, phone FROM vets WHERE county = '$county' LIMIT 1";
$vet_result = $conn->query($vet_sql);

$vet_info = null;
if ($vet_result && $vet_result->num_rows > 0) {
    $vet_info = $vet_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PestFree Livestock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="script.css">
</head>
<body>
    <!-- Navigation (reuse from index.html) -->
     <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="API Icon" width="24" height="24" >LPD Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vets">Emergency Vets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#diseases">Diseases</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pests">Pests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#prevention">Prevention</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center ms-lg-3">
    <span class="text-white me-3">
        <i class="fas fa-user-circle me-1"></i>
        <?php echo htmlspecialchars($_SESSION['name']); ?>
    </span>
    <a href="logout.php" class="btn btn-outline-warning">Log Out</a>
</div>
        </div>
    </nav>

      <!-- Animal Details Form -->
<div class="container my-4">
    <h4>Add Animal Details</h4>
    <form action="add_animal.php" method="POST" class="row g-3">
        <div class="col-md-3">
            <input type="text" class="form-control" name="animal_type" placeholder="Animal Type (e.g. Cow)" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="breed" placeholder="Breed (e.g. Friesian)" required>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control" name="age" placeholder="Age (years)" min="0" required>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="health_status" placeholder="Health Status" required>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="notes" placeholder="Notes">
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-success w-100">Add Animal</button>
        </div>
    </form>
</div>

<?php
// Get farmer's phone
$farmer_name = $_SESSION['name'];
$sql = "SELECT phone FROM farmers WHERE name = '$farmer_name' LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$farmer_phone = $row['phone'];

// Fetch animals
$animals = $conn->query("SELECT * FROM animals WHERE farmer_phone = '$farmer_phone' ORDER BY date_added DESC");
?>
<div class="container my-4">
    <h4>Your Animals</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Breed</th>
                <th>Age</th>
                <th>Health Status</th>
                <th>Notes</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php while($animal = $animals->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($animal['animal_type']); ?></td>
                <td><?php echo htmlspecialchars($animal['breed']); ?></td>
                <td><?php echo htmlspecialchars($animal['age']); ?></td>
                <td><?php echo htmlspecialchars($animal['health_status']); ?></td>
                <td><?php echo htmlspecialchars($animal['notes']); ?></td>
                <td><?php echo htmlspecialchars($animal['date_added']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

  <!-- Animal History Reporting Form -->
<div class="container my-4">
    <h4>Report Animal Disease or Pest Case</h4>
    <form action="report_history.php" method="POST" class="row g-3">
        <div class="col-md-3">
            <input type="text" class="form-control" name="animal_type" placeholder="Animal Type (e.g. Cattle)" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="disease" placeholder="Disease or Pest" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="county" placeholder="County" required>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100">Report</button>
        </div>
    </form>
</div>

<?php
// Fetch latest disease alerts (separate from previous queries)
$disease_alerts = $conn->query("SELECT * FROM animal_history ORDER BY date_reported DESC LIMIT 6");
?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Current Disease Alerts</h2>
            <p class="lead text-muted">Recent outbreaks reported by farmers</p>
        </div>
        <div class="row g-4">
            <?php while($alert = $disease_alerts->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="alert-item">
                    <h4>
                        <?php echo htmlspecialchars($alert['disease']); ?>
                        <span class="badge bg-danger">Reported</span>
                    </h4>
                    <p class="mb-1">Animal: <?php echo htmlspecialchars($alert['animal_type']); ?></p>
                    <p class="mb-1">County: <?php echo htmlspecialchars($alert['county']); ?></p>
                    <small class="text-muted">By: <?php echo htmlspecialchars($alert['user_name']); ?> | <?php echo htmlspecialchars($alert['date_reported']); ?></small>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

    <!-- Find Veterinarian Section -->
   <section id="vets" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Emergency Veterinary Contacts</h2>
                <p class="lead text-muted">Immediate help for your livestock</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card p-4">
                        <h3 class="mb-4">Find Nearest Veterinarian</h3>
                        <form id="vetSearchForm">
                            <div class="mb-3">
                                <label for="county" class="form-label">County</label>
                                <select class="form-select" id="county" name="county" required>
                                    <option value="" selected disabled>Select your county</option>
                                    <option value="Nairobi">Nairobi</option>
                                    <option value="Kiambu">Kiambu</option>
                                    <option value="Nakuru">Nakuru</option>
                                    <option value="Kisumu">Kisumu</option>
                                    <option value="Mombasa">Mombasa</option>
                                    <option value="Meru">Meru</option>
                                    <option value="Kakamega">Kakamega</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="animalType" class="form-label">Animal Type</label>
                                <select class="form-select" id="animalType" name="animal_type" required>
                                    <option value="" selected disabled>Select animal type</option>
                                    <option value="Cattle">Cattle</option>
                                    <option value="Poultry">Poultry</option>
                                    <option value="Goats">Goats</option>
                                    <option value="Sheep">Sheep</option>
                                    <option value="Pigs">Pigs</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Find Vets
                            </button>
                        </form>
                        <div id="vetResult" class="mt-3"></div>
                        
                        <div class="emergency-banner mt-4">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i>Emergency Hotlines</h4>
                            <p class="mb-1">National Veterinary Service: <strong>0800 720 220</strong></p>
                            <p>Animal Disease Hotline: <strong>0800 721 221</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card p-4">
                        <h3 class="mb-4">Vet Details</h3>
                        <div id="vetDetails">
                <p class="text-muted">Click "Find Vets" to see details here.</p>
            </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- Disease Identification Section -->
    <section id="diseases" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Livestock Disease Identification</h2>
                <p class="lead text-muted">Recognize symptoms and find treatments</p>
            </div>
            
            <div class="text-center mb-4">
                <h4>Select Your Animal</h4>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <div class="animal-selector p-3 rounded-3 text-center" data-animal="cattle">
                        <i class="fas fa-cow fa-3x mb-2"></i>
                        <p class="mb-0 fw-bold">Cattle</p>
                    </div>
                    <div class="animal-selector p-3 rounded-3 text-center" data-animal="poultry">
                        <i class="fas fa-kiwi-bird fa-3x mb-2"></i>
                        <p class="mb-0 fw-bold">Poultry</p>
                    </div>
                    <div class="animal-selector p-3 rounded-3 text-center" data-animal="goats">
                        <i class="fas fa-goat fa-3x mb-2"></i>
                        <p class="mb-0 fw-bold">Goats</p>
                    </div>
                    <div class="animal-selector p-3 rounded-3 text-center" data-animal="sheep">
                        <i class="fas fa-sheep fa-3x mb-2"></i>
                        <p class="mb-0 fw-bold">Sheep</p>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card disease-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4>East Coast Fever (ECF)</h4>
                                <span class="badge bg-danger">High Risk</span>
                            </div>
                            <p class="text-muted">Affects: Cattle</p>
                            
                            <h5 class="mt-3">Symptoms</h5>
                            <div class="symptom-list">
                                <div class="symptom-item">High fever (104-107°F)</div>
                                <div class="symptom-item">Swollen lymph nodes</div>
                                <div class="symptom-item">Difficulty breathing</div>
                                <div class="symptom-item">Loss of appetite</div>
                                <div class="symptom-item">Watery eyes and nasal discharge</div>
                            </div>
                            
                            <h5 class="mt-3">Treatment Protocol</h5>
                            <div class="treatment-protocol">
                                <ol>
                                    <li>Immediately isolate affected animal</li>
                                    <li>Contact veterinarian for specific treatment</li>
                                    <li>Buparvaquone injections (prescription required)</li>
                                    <li>Supportive care with fluids and anti-inflammatory drugs</li>
                                </ol>
                                <p class="mb-0"><strong>Mortality Rate:</strong> 30-100% if untreated</p>
                            </div>
                            
                            <button class="btn btn-primary mt-3">
                                <i class="fas fa-book-medical me-2"></i> Full Treatment Guide
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card disease-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4>Newcastle Disease</h4>
                                <span class="badge bg-warning">Medium Risk</span>
                            </div>
                            <p class="text-muted">Affects: Poultry</p>
                            
                            <h5 class="mt-3">Symptoms</h5>
                            <div class="symptom-list">
                                <div class="symptom-item">Sneezing, coughing, nasal discharge</div>
                                <div class="symptom-item">Greenish, watery diarrhea</div>
                                <div class="symptom-item">Depression, loss of appetite</div>
                                <div class="symptom-item">Nervous signs (tremors, paralysis)</div>
                                <div class="symptom-item">Sudden death in severe cases</div>
                            </div>
                            
                            <h5 class="mt-3">Treatment Protocol</h5>
                            <div class="treatment-protocol">
                                <ol>
                                    <li>No specific treatment for virus</li>
                                    <li>Isolate and humanely cull infected birds</li>
                                    <li>Vaccinate healthy birds (I2 or Lasota vaccine)</li>
                                    <li>Disinfect housing and equipment thoroughly</li>
                                </ol>
                                <p class="mb-0"><strong>Mortality Rate:</strong> Up to 100% in unvaccinated flocks</p>
                            </div>
                            
                            <button class="btn btn-primary mt-3">
                                <i class="fas fa-book-medical me-2"></i> Full Treatment Guide
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="#" class="btn btn-outline-primary">View All Cattle Diseases</a>
            </div>
        </div>
    </section>

    <!-- Pest Management Section -->
    <section id="pests" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Pest Control Solutions</h2>
                <p class="lead text-muted">Protect your livestock from parasites and pests</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card pest-card">
                        <div class="card-body">
                            <h4>Ticks</h4>
                            <p class="text-muted">Affects: Cattle, Goats, Sheep</p>
                            
                            <h5 class="mt-3">Signs of Infestation</h5>
                            <ul>
                                <li>Visible ticks on skin (ears, udder, tail)</li>
                                <li>Anemia (pale gums, weakness)</li>
                                <li>Skin damage and irritation</li>
                                <li>Reduced milk production</li>
                            </ul>
                            
                            <h5 class="mt-3">Control Methods</h5>
                            <ul>
                                <li>Regular acaricide dipping/spraying</li>
                                <li>Rotational grazing</li>
                                <li>Manual removal with gloves</li>
                                <li>Biological control (chickens, guinea fowl)</li>
                            </ul>
                            
                            <div class="d-grid mt-3">
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-spray-can me-2"></i> Recommended Acaricides
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card pest-card">
                        <div class="card-body">
                            <h4>Mites & Lice</h4>
                            <p class="text-muted">Affects: Poultry, Cattle, Goats</p>
                            
                            <h5 class="mt-3">Signs of Infestation</h5>
                            <ul>
                                <li>Feather/skin damage and loss</li>
                                <li>Excessive scratching and rubbing</li>
                                <li>Restlessness, reduced productivity</li>
                                <li>Visible parasites at base of feathers/hair</li>
                            </ul>
                            
                            <h5 class="mt-3">Control Methods</h5>
                            <ul>
                                <li>Dusting with approved insecticides</li>
                                <li>Spraying with pyrethroid-based products</li>
                                <li>Clean and disinfect housing regularly</li>
                                <li>Provide dust baths for poultry</li>
                            </ul>
                            
                            <div class="d-grid mt-3">
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-spray-can me-2"></i> Recommended Treatments
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card pest-card">
                        <div class="card-body">
                            <h4>Tsetse Flies</h4>
                            <p class="text-muted">Affects: Cattle, Goats, Pigs</p>
                            
                            <h5 class="mt-3">Signs of Infestation</h5>
                            <ul>
                                <li>Visible flies biting animals</li>
                                <li>Transmit trypanosomiasis (nagana)</li>
                                <li>Anemia, weight loss, reduced productivity</li>
                                <li>Swollen lymph nodes</li>
                            </ul>
                            
                            <h5 class="mt-3">Control Methods</h5>
                            <ul>
                                <li>Use of insecticide-treated screens/traps</li>
                                <li>Pour-on insecticides</li>
                                <li>Bush clearing around homesteads</li>
                                <li>Sterile insect technique (government programs)</li>
                            </ul>
                            
                            <div class="d-grid mt-3">
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-spray-can me-2"></i> Recommended Repellents
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="#" class="btn btn-outline-primary">View All Pest Control Methods</a>
            </div>
        </div>
    </section>

    <!-- Prevention Section -->
     <section id="prevention" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Prevention Strategies</h2>
                <p class="lead text-muted">Proactive measures to protect your livestock</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card prevention-card">
                        <div class="card-body">
                            <h4>Vaccination Schedule</h4>
                            <p>Follow this recommended vaccination timeline for your livestock:</p>
                            
                            <h5 class="mt-3">Cattle</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Disease</th>
                                        <th>First Dose</th>
                                        <th>Boosters</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>East Coast Fever</td>
                                        <td>3 months</td>
                                        <td>Annual</td>
                                    </tr>
                                    <tr>
                                        <td>Foot and Mouth</td>
                                        <td>4 months</td>
                                        <td>Every 6 months</td>
                                    </tr>
                                    <tr>
                                        <td>Anthrax</td>
                                        <td>6 months</td>
                                        <td>Annual</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <h5 class="mt-3">Poultry</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Disease</th>
                                        <th>First Dose</th>
                                        <th>Boosters</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Newcastle</td>
                                        <td>Day 1</td>
                                        <td>Every 3 months</td>
                                    </tr>
                                    <tr>
                                        <td>Gumboro</td>
                                        <td>2 weeks</td>
                                        <td>Every 2 months</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card prevention-card">
                        <div class="card-body">
                            <h4>Biosecurity Measures</h4>
                            <p>Essential practices to prevent disease introduction and spread:</p>
                            
                            <div class="accordion mt-3" id="biosecurityAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                            Farm Entry Protocols
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#biosecurityAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Disinfectant foot baths at all entry points</li>
                                                <li>Restrict visitor access to animal areas</li>
                                                <li>Clean clothing and boots for workers</li>
                                                <li>Vehicle disinfection when entering</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                            New Animal Introduction
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#biosecurityAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Quarantine new animals for 30 days</li>
                                                <li>Test for major diseases before introduction</li>
                                                <li>Vaccinate before mixing with herd/flock</li>
                                                <li>Observe for signs of illness</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                            Sanitation Practices
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#biosecurityAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Daily removal of manure and soiled bedding</li>
                                                <li>Weekly disinfection of housing</li>
                                                <li>Proper disposal of dead animals</li>
                                                <li>Clean feeders and waterers daily</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-4">Pest Control Schedule</h5>
                            <ul>
                                <li><strong>Monthly:</strong> Spray/dip for ticks and flies</li>
                                <li><strong>Quarterly:</strong> Deworm all animals</li>
                                <li><strong>Seasonal:</strong> Control mosquitoes and biting flies</li>
                                <li><strong>As needed:</strong> Rodent control measures</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Subscribe Modal -->
    <div class="modal fade" id="subscribeModal" tabindex="-1" aria-labelledby="subscribeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscribeModalLabel">Subscribe to Alerts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Get disease outbreak alerts, prevention tips, and veterinary service updates delivered to your phone.</p>
                    <form>
                        <div class="mb-3">
                            <label for="subName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="subName">
                        </div>
                        <div class="mb-3">
                            <label for="subPhone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="subPhone" placeholder="e.g. 0712345678">
                        </div>
                        <div class="mb-3">
                            <label for="subCounty" class="form-label">County</label>
                            <select class="form-select" id="subCounty">
                                <option selected disabled>Select your county</option>
                                <option>Nairobi</option>
                                <option>Kiambu</option>
                                <option>Nakuru</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alert Preferences</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="subDiseaseAlerts" checked>
                                <label class="form-check-label" for="subDiseaseAlerts">Disease Outbreaks</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="subPreventionTips" checked>
                                <label class="form-check-label" for="subPreventionTips">Prevention Tips</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="subVetUpdates">
                                <label class="form-check-label" for="subVetUpdates">Veterinary Service Updates</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.getElementById('vetSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const county = document.getElementById('county').value;
    const animalType = document.getElementById('animalType').value;

    fetch('find_vet.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `county=${encodeURIComponent(county)}&animal_type=${encodeURIComponent(animalType)}`
    })
    .then(response => response.json())
    .then(data => {
        const vetDetails = document.getElementById('vetDetails');
        if (data.success) {
            vetDetails.innerHTML = `
                <p><strong>Name:</strong> ${data.name}</p>
                <p><strong>Phone:</strong> ${data.phone}</p>
            `;
        } else {
            vetDetails.innerHTML = `<p class="text-danger">${data.message}</p>`;
        }
    });
});
</script>
</body>
</html>