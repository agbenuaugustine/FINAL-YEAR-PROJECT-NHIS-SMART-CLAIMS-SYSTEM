// Simple Claims Loader - Drop-in replacement
// Copy this code and add it to your page, or replace the existing loadConsultations function

// Simple function to load consultations
async function loadConsultationsSimple() {
    console.log('=== Simple loadConsultations called ===');
    
    const tableBody = document.getElementById('consultations_table');
    if (!tableBody) {
        alert('Error: consultations_table element not found');
        return;
    }
    
    // Show loading
    tableBody.innerHTML = `
        <tr>
            <td colspan="10" style="text-align: center; padding: 20px;">
                <i class="fas fa-spinner fa-spin"></i> Loading consultations...
            </td>
        </tr>
    `;
    
    try {
        const response = await fetch('api/claims-api.php?action=get_claimable_consultations');
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        console.log('API Response:', result);
        
        if (result.status === 'success' && result.data) {
            const consultations = result.data;
            console.log(`Found ${consultations.length} consultations`);
            
            if (consultations.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 20px;">
                            No claimable consultations found
                        </td>
                    </tr>
                `;
                return;
            }
            
            // Clear table and populate
            tableBody.innerHTML = '';
            
            consultations.forEach(consultation => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="radio" name="consultation" value="${consultation.visit_id}"></td>
                    <td>${consultation.visit_id}</td>
                    <td>${consultation.full_name}</td>
                    <td>${consultation.nhis_number || 'N/A'}</td>
                    <td>${new Date(consultation.visit_date).toLocaleDateString()}</td>
                    <td>${consultation.department_name || 'General'}</td>
                    <td>${consultation.physician_name || 'Not assigned'}</td>
                    <td>
                        <div>Diagnoses: ${consultation.diagnosis_count || 0}</div>
                        <div>Medications: ${consultation.prescription_count || 0}</div>
                    </td>
                    <td><span style="color: green;">Available</span></td>
                    <td>
                        <button onclick="alert('View consultation ${consultation.visit_id}')" style="padding: 5px 10px;">
                            View
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Update status if element exists
            const statusIndicator = document.getElementById('statusIndicator');
            if (statusIndicator) {
                statusIndicator.innerHTML = `<i class="fas fa-check-circle"></i> ${consultations.length} consultations loaded`;
                statusIndicator.className = statusIndicator.className.replace(/bg-\w+-\d+/g, '').replace(/text-\w+-\d+/g, '') + ' bg-green-100 text-green-800';
            }
            
        } else {
            throw new Error(result.message || 'API returned error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: red;">
                    Error: ${error.message}
                    <br><br>
                    <button onclick="loadConsultationsSimple()" style="padding: 5px 10px;">
                        Try Again
                    </button>
                </td>
            </tr>
        `;
        
        // Update status if element exists
        const statusIndicator = document.getElementById('statusIndicator');
        if (statusIndicator) {
            statusIndicator.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Error loading consultations`;
            statusIndicator.className = statusIndicator.className.replace(/bg-\w+-\d+/g, '').replace(/text-\w+-\d+/g, '') + ' bg-red-100 text-red-800';
        }
    }
}

// Override the existing function
function loadConsultations() {
    return loadConsultationsSimple();
}

// Make sure the function is available globally
window.loadConsultations = loadConsultations;
window.loadConsultationsSimple = loadConsultationsSimple;