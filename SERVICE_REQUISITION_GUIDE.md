# Service Requisition System Guide

## Button Functions Explained

### 🔵 **Submit Requisition** (Blue Button)
- **Purpose**: Officially processes the service request in the system
- **Action**: Saves data to database and creates visit record
- **Database Impact**: 
  - Creates record in `visits` table
  - Creates records in `service_orders` table
  - Generates unique visit number (e.g., V2024000001)
- **Result**: Patient visit is officially registered in system
- **Next Steps**: System suggests proceeding to vital signs recording
- **Use When**: Ready to officially register the patient visit

### 🟡 **Generate Form** (Yellow Button)
- **Purpose**: Creates printable NHIS requisition form for documentation
- **Action**: Opens formatted document in new browser window
- **Database Impact**: None - no data saved
- **Result**: Professional NHIS-compliant form that can be printed/saved as PDF
- **Next Steps**: Print for patient records or physical documentation
- **Use When**: Need physical documentation or patient copy

### 🔘 **Save Draft** (Gray Button - in cart area)
- **Purpose**: Temporarily saves work-in-progress
- **Action**: Stores data in browser's local storage
- **Database Impact**: None - saved locally only
- **Result**: Can resume work later without losing progress
- **Limit**: Keeps last 10 drafts automatically
- **Use When**: Need to save progress but not ready to submit

### 📁 **View Drafts** (Gray Button - top right)
- **Purpose**: Access all saved drafts
- **Action**: Opens modal showing all saved drafts
- **Features**:
  - Shows draft count as red badge when drafts exist
  - Load any draft back into the form
  - View detailed breakdown of each draft
  - Delete unwanted drafts
- **Use When**: Want to continue previous work or review saved drafts

## Workflow Recommendations

### **Typical Usage Pattern:**
1. **Start Work**: Select patient and services
2. **Save Draft**: If need to pause work - use "Save Draft"
3. **Generate Form**: Create printable form for records - use "Generate Form"
4. **Submit Requisition**: When ready to process officially - use "Submit Requisition"

### **Draft Management:**
- Drafts are saved with timestamp and creator name
- Each draft gets unique ID for easy identification
- Maximum 10 drafts stored (oldest automatically deleted)
- Drafts persist until browser data is cleared

## Key Differences Summary

| Feature | Submit Requisition | Generate Form | Save Draft | View Drafts |
|---------|-------------------|---------------|------------|-------------|
| **Database** | ✅ Creates records | ❌ No impact | ❌ No impact | ❌ No impact |
| **Printable** | ❌ No | ✅ Yes | ❌ No | ❌ No |
| **Trackable** | ✅ Visit number | ❌ No | ❌ No | ❌ No |
| **Reversible** | ❌ Permanent | ✅ Just document | ✅ Can delete | ✅ Can delete |
| **Next Steps** | ✅ Vital signs | ❌ None | ❌ None | ❌ None |

## Best Practices

### **For Healthcare Providers:**
- Use **Save Draft** frequently during long sessions
- **Generate Form** for patient copies and physical records
- **Submit Requisition** only when completely ready
- Use **View Drafts** to manage multiple patients

### **For Administrative Staff:**
- **Generate Form** for filing and documentation
- **Submit Requisition** to ensure proper system tracking
- Monitor draft usage to help staff complete pending work

### **For System Administration:**
- Submitted requisitions appear in "Recent Requisitions" table
- All submitted requisitions flow to Claims Processing
- Drafts are user-specific and stored locally
- Consider periodic draft cleanup policies

## Technical Notes

- **Drafts Storage**: Browser localStorage (user-specific)
- **Draft Limit**: 10 most recent drafts automatically maintained
- **Form Generation**: Opens in new window for printing/PDF saving
- **Submission Validation**: Requires patient selection and at least one service
- **Error Handling**: Improved network error messages and timeout handling

## Troubleshooting

### **"Submission failed" Error:**
- Check internet connection
- Verify session hasn't expired
- Try refreshing page and re-submitting

### **Drafts Not Showing:**
- Drafts are browser-specific
- Clearing browser data removes drafts
- Different computers/browsers have separate drafts

### **Form Not Generating:**
- Ensure popup blocker is disabled
- Select patient and services first
- Try different browser if issues persist