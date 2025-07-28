        // Sample veterinarian data
        const veterinarians = {
            nairobi: [
                { name: "Nairobi Vet Clinic", animal: "cattle", phone: "0721000001", location: [-1.286389, 36.817223], address: "Moi Avenue, Nairobi", emergency: true },
                { name: "City Poultry Specialists", animal: "poultry", phone: "0721000002", location: [-1.276, 36.807], address: "Tom Mboya St, Nairobi", emergency: false },
                { name: "Livestock Health Center", animal: "goats", phone: "0721000003", location: [-1.296, 36.827], address: "Ngong Road, Nairobi", emergency: true },
                { name: "Nairobi Animal Hospital", animal: "cattle", phone: "0721000004", location: [-1.266, 36.807], address: "Kenyatta Avenue, Nairobi", emergency: true }
            ],
            kiambu: [
                { name: "Kiambu Animal Hospital", animal: "cattle", phone: "0722000001", location: [-1.1715, 36.8355], address: "Kiambu Town", emergency: true },
                { name: "Poultry Care Kiambu", animal: "poultry", phone: "0722000002", location: [-1.1615, 36.8255], address: "Near Kiambu Market", emergency: false },
                { name: "Ruiru Vet Services", animal: "goats", phone: "0722000003", location: [-1.1515, 36.8455], address: "Ruiru Town", emergency: true }
            ],
            nakuru: [
                { name: "Nakuru Veterinary Services", animal: "cattle", phone: "0723000001", location: [-0.303099, 36.080025], address: "Kenyatta Avenue, Nakuru", emergency: true },
                { name: "Rift Valley Goat Clinic", animal: "goats", phone: "0723000002", location: [-0.313099, 36.090025], address: "Mbaruk Road, Nakuru", emergency: false },
                { name: "Nakuru Poultry Experts", animal: "poultry", phone: "0723000003", location: [-0.293099, 36.070025], address: "Oginga Odinga Road", emergency: false }
            ]
        };

        // Disease data
        const diseases = {
            cattle: [
                {
                    name: "East Coast Fever (ECF)",
                    risk: "High",
                    symptoms: ["High fever (104-107°F)", "Swollen lymph nodes", "Difficulty breathing", "Loss of appetite", "Watery eyes and nasal discharge"],
                    treatment: ["Immediately isolate affected animal", "Contact veterinarian for specific treatment", "Buparvaquone injections (prescription required)", "Supportive care with fluids and anti-inflammatory drugs"],
                    mortality: "30-100% if untreated"
                },
                {
                    name: "Lumpy Skin Disease",
                    risk: "High",
                    symptoms: ["Fever", "Skin nodules", "Lacrimation", "Nasal discharge", "Reduced milk production"],
                    treatment: ["Isolate infected animals", "Supportive treatment", "Antibiotics for secondary infections", "Vaccination of healthy animals"],
                    mortality: "1-5%"
                }
            ],
            poultry: [
                {
                    name: "Newcastle Disease",
                    risk: "Medium",
                    symptoms: ["Sneezing, coughing, nasal discharge", "Greenish, watery diarrhea", "Depression, loss of appetite", "Nervous signs (tremors, paralysis)", "Sudden death in severe cases"],
                    treatment: ["No specific treatment for virus", "Isolate and humanely cull infected birds", "Vaccinate healthy birds (I2 or Lasota vaccine)", "Disinfect housing and equipment thoroughly"],
                    mortality: "Up to 100% in unvaccinated flocks"
                }
            ],
            goats: [
                {
                    name: "Contagious Caprine Pleuropneumonia (CCPP)",
                    risk: "High",
                    symptoms: ["High fever", "Difficulty breathing", "Coughing", "Nasal discharge", "Loss of appetite"],
                    treatment: ["Antibiotics (tylosin, oxytetracycline)", "Isolate sick animals", "Vaccinate healthy animals", "Improve ventilation in housing"],
                    mortality: "60-100% in acute cases"
                }
            ]
        };

        // Initialize map (this would be replaced with actual map API in production)
        function initMap() {
            const mapElement = document.getElementById('veterinarianMap');
            if (mapElement) {
                mapElement.innerHTML = '<div class="p-4 text-center text-muted"><i class="fas fa-map-marked-alt fa-3x mb-3"></i><p>Map will display here when veterinarians are found</p></div>';
            }
        }

        // Handle vet search form submission
        document.getElementById('vetSearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const county = document.getElementById('county').value;
            const animalType = document.getElementById('animalType').value;
            const emergencyOnly = document.getElementById('emergencyOnly').checked;
            
            if (county && animalType) {
                let results = veterinarians[county]?.filter(vet => vet.animal === animalType) || [];
                
                if (emergencyOnly) {
                    results = results.filter(vet => vet.emergency);
                }
                
                if (results.length > 0) {
                    let html = '<div class="list-group">';
                    results.forEach(vet => {
                        const emergencyBadge = vet.emergency ? '<span class="badge bg-danger ms-2">24/7</span>' : '';
                        html += `
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">${vet.name} ${emergencyBadge}</h5>
                                    <small><i class="fas fa-phone me-1"></i>${vet.phone}</small>
                                </div>
                                <p class="mb-1">${vet.address}</p>
                                <small><a href="#" class="text-primary"><i class="fas fa-directions me-1"></i>Get directions</a></small>
                            </div>
                        `;
                    });
                    html += '</div>';
                    
                    document.getElementById('vetResults').innerHTML = html;
                    
                    // In a real app, we would show the map with markers here
                    document.getElementById('veterinarianMap').innerHTML = `
                        <div class="p-4 text-center text-success">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>${results.length} veterinarians found</p>
                            <small class="text-muted">Map integration would show locations here</small>
                        </div>
                    `;
                } else {
                    document.getElementById('vetResults').innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No veterinarians found matching your criteria.
                            ${emergencyOnly ? 'Try without "Emergency Only" filter.' : 'Try a different county or animal type.'}
                        </div>
                    `;
                }
            }
        });

        // Handle animal selection for education
        const animalSelectors = document.querySelectorAll('.animal-selector');
        animalSelectors.forEach(selector => {
            selector.addEventListener('click', function() {
                animalSelectors.forEach(s => s.classList.remove('active', 'bg-primary', 'text-white'));
                this.classList.add('active');
                
                const animal = this.getAttribute('data-animal');
                loadDiseaseContent(animal);
            });
        });

        // Load disease content based on animal type
        function loadDiseaseContent(animal) {
            const animalDiseases = diseases[animal] || [];
            
            if (animalDiseases.length > 0) {
                let html = '';
                animalDiseases.forEach(disease => {
                    const riskClass = disease.risk === "High" ? "danger" : disease.risk === "Medium" ? "warning" : "success";
                    
                    html += `
                        <div class="col-md-6">
                            <div class="card disease-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h4>${disease.name}</h4>
                                        <span class="badge bg-${riskClass}">${disease.risk} Risk</span>
                                    </div>
                                    <p class="text-muted">Affects: ${animal.charAt(0).toUpperCase() + animal.slice(1)}</p>
                                    
                                    <h5 class="mt-3">Symptoms</h5>
                                    <div class="symptom-list">
                                        ${disease.symptoms.map(s => `<div class="symptom-item">${s}</div>`).join('')}
                                    </div>
                                    
                                    <h5 class="mt-3">Treatment Protocol</h5>
                                    <div class="treatment-protocol">
                                        <ol>
                                            ${disease.treatment.map(t => `<li>${t}</li>`).join('')}
                                        </ol>
                                        <p class="mb-0"><strong>Mortality Rate:</strong> ${disease.mortality}</p>
                                    </div>
                                    
                                    <button class="btn btn-primary mt-3">
                                        <i class="fas fa-book-medical me-2"></i> Full Treatment Guide
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                document.querySelector('#diseases .row.g-4').innerHTML = html;
            }
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            // Set default animal selection (cattle)
            document.querySelector('.animal-selector[data-animal="cattle"]').click();
        });