# Post-Rebuild Checklist

## ✅ Files to Delete (old plugin remnants)
- [ ] `jquery-ui.css`
- [ ] `popup-modal.css`
- [ ] `popup-modal.js`
- [ ] `template.php`
- [ ] `readme.txt`

## ✅ Plugin Status
- [ ] Deactivate plugin in WordPress
- [ ] Reactivate plugin
- [ ] Check for any PHP errors

## ✅ Test Existing Popups
- [ ] Go to Popups in admin
- [ ] Edit an existing popup
- [ ] Verify settings show correctly:
  - Modal Size dropdown
  - Background Color picker
  - Background Opacity field
  - Close Button Position
  - Close Button Style
- [ ] Check trigger code box displays both methods
- [ ] Save popup

## ✅ Frontend Test
- [ ] View page with popup trigger
- [ ] Click trigger - popup should open
- [ ] ESC key should close
- [ ] Click overlay should close
- [ ] Check mobile responsive

## ✅ Create New Test Popup
- [ ] Popups → Add New
- [ ] Add some test content
- [ ] Set size to "Large"
- [ ] Pick background color
- [ ] Set opacity to 0.9
- [ ] Choose close position
- [ ] Publish
- [ ] Copy trigger class from code box
- [ ] Add to WPBakery button
- [ ] Test frontend

## 🚨 If Something Breaks
1. Check browser console for JS errors
2. Check PHP error logs
3. Verify all files are in correct locations
4. Check file permissions
5. Clear any caching plugins

## Notes
- Post type is `hsp_popup` (unchanged)
- All existing popups should still work
- Old settings meta keys match new ones
- Trigger classes are backward compatible
