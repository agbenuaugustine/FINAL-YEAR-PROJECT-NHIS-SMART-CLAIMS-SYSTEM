// QUICK FIX: Replace the loadConsultations function with this simplified version
// Copy this code and replace the existing loadConsultations function

async function loadConsultations() {
    console.log('=== QUICK FIX: loadConsultations() called ===');
    
    const tableBody = document.getElementById('consultations_table');
    if (!tableBody) {
        console.error('Table element not found!');
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
        console.log('Making API request...');
        const response = await fetch('api/claims-api.php?action=get_claimable_consultations');
        
        console.log('Response received:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        console.log('API Result:', result);
        
        if (result.status === 'success' && result.data) {
            console.log(`Found ${result.data.length} consultations`);
            
            if (result.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 20px;">
                            No claimable consultations found
                        </td>
                    </tr>
                `;
                return;
            }
            
            // Clear table and populate with data
            tableBody.innerHTML = '';
            
            result.data.forEach(consultation => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="radio" name="selected_consultation" value="${consultation.visit_id}">
                    </td>
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
                    <td>
                        <span class="badge badge-success">Available</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="alert('View details for visit ${consultation.visit_id}')">
                            View
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Update status indicator
            const statusIndicator = document.getElementById('statusIndicator');
            if (statusIndicator) {
                statusIndicator.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
                statusIndicator.innerHTML = '<i class="fas fa-check-circle mr-1"></i>System Ready - ' + result.data.length + ' consultations';
            }
            
        } else {
            throw new Error(result.message || 'API returned error status');
        }
        
    } catch (error) {
        console.error('Error loading consultations:', error);
        
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: red;">
                    <div>Error: ${error.message}</div>
                    <button onclick="loadConsultations()" style="margin-top: 10px; padding: 5px 10px;">
                        Retry
                    </button>
                </td>
            </tr>
        `;
        
        // Update status indicator
        const statusIndicator = document.getElementById('statusIndicator');
        if (statusIndicator) {
            statusIndicator.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
            statusIndicator.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Error loading consultations';
        }
    }
}