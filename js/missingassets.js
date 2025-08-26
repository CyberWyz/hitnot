 // Modal functions
 function openRecoveryModal(assetId) {
     document.getElementById('asset_id').value = assetId;
     document.getElementById('recoveryModal').style.display = 'block';
 }

 function closeRecoveryModal() {
     document.getElementById('recoveryModal').style.display = 'none';
 }

 // Close modal when clicking outside of it
 window.onclick = function(event) {
     const modal = document.getElementById('recoveryModal');
     if (event.target == modal) {
         closeRecoveryModal();
     }
 }