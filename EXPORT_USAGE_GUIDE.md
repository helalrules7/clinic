# Patient Data Export Feature - Usage Guide

## Overview
The patient data export feature allows authorized users (doctors and admins) to export complete patient information as a formatted Word document.

## How to Use

### 1. Access the Feature
1. Log in as a doctor or admin
2. Navigate to a patient's profile page (`/doctor/patients/{id}`)
3. Click the "Export Data" button in the dropdown menu

### 2. Export Process
The export includes:
- **Patient Information**: Basic details, contact info, emergency contacts
- **Medical History**: Both new format and legacy medical history entries
- **Recent Appointments**: Last 10 appointments with details
- **Medical Notes**: All patient notes with timestamps
- **Glasses Prescriptions**: Complete prescription history
- **Files & Images**: Patient attachments with embedded images (resized to 400x400px max)

### 3. Generated Document Format
- **File Type**: Microsoft Word (.docx)
- **Filename**: `Patient_{ID}_{DATE}.docx`
- **Language**: English
- **Layout**: Professional formatting with colored tables for different sections

## Technical Details

### API Endpoints
- `GET /api/patients/{id}/export` - Download patient data as Word document
- `HEAD /api/patients/{id}/export` - Check access permissions

### Authentication Required
- User must be logged in
- User role must be 'doctor' or 'admin'

### File Processing
- Images are automatically resized to maximum 400x400 pixels
- Only image files under 5MB are embedded
- Supported image formats: JPEG, PNG, GIF, BMP
- Temporary files are automatically cleaned up

### Security Features
- Authentication and authorization checks
- Session-based access control
- Automatic cleanup of temporary files
- No sensitive data exposure in URLs

## Error Handling

### Common Issues
1. **401 Unauthorized**: User not logged in
   - Solution: Refresh the page and log in again

2. **403 Forbidden**: User doesn't have permission
   - Solution: Contact admin to verify your role permissions

3. **404 Not Found**: Patient doesn't exist
   - Solution: Verify the patient ID is correct

4. **500 Server Error**: Technical issue
   - Solution: Contact technical support

### JavaScript Error Messages
- "You must be logged in to export patient data"
- "Error accessing export function"
- "Network error. Please check your connection"

## Browser Compatibility
- Chrome (recommended)
- Firefox
- Safari
- Edge

## File Size Considerations
- Average export size: 50KB - 5MB depending on content
- Images are compressed to maintain reasonable file sizes
- Large patient histories may take 5-10 seconds to generate

## Troubleshooting

### Common Issues

**401 Unauthorized Error:**
- **Most Common Issue**: You need to be logged in first
- Go to `/login` and sign in as a doctor or admin
- Then navigate to the patient page and try export again

**403 Permission Denied:**
- Only doctors and admins can export patient data
- Secretary role does not have export permissions

### If Export Button Doesn't Work
1. Check browser console for JavaScript errors
2. Verify you're logged in with correct permissions (doctor/admin)
3. Try refreshing the page and logging in again
4. Check network connectivity

### If Download Doesn't Start
1. Check browser download settings
2. Disable popup blockers for the clinic site
3. Try a different browser
4. Clear browser cache

### Fixed Issues in Latest Version:
- ✅ Fixed SQL column name mismatches (updated to match actual database schema)
- ✅ Corrected patient_notes table references  
- ✅ Updated user name field handling
- ✅ Improved error handling and debugging
- ✅ Fixed table header text color (changed from white to black for better readability)
- ✅ Fixed image insertion (now shows actual images instead of just filenames)
- ✅ Improved image path resolution and error handling
- ✅ Enhanced image preview with proper labeling and sizing
- ✅ Fixed "picture can't be displayed" issue in Word documents
- ✅ Improved image file type detection (checks both extension and MIME type)
- ✅ Enhanced image resizing (always converts to JPEG for Word compatibility)
- ✅ Better aspect ratio preservation in display sizing
- ✅ Added comprehensive error logging for debugging
- ✅ Improved cleanup of temporary files
- ✅ **MAJOR FIX**: Rewrote image embedding according to PHPWord documentation
- ✅ Added white background for JPEG conversion (crucial for Word compatibility)
- ✅ Improved temporary file management with proper cleanup
- ✅ Enhanced image quality with 90% JPEG compression
- ✅ Added proper image accessibility checks (file_exists + is_readable)

### For Developers
- Check server logs for detailed error messages: `/var/log/apache2/roaya-clinic_error.log`
- Verify PHPWord library is properly installed
- Ensure GD extension is enabled for image processing
- Check file permissions on storage directories

## Support
For technical issues or feature requests, contact the system administrator.
