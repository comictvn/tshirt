<script type='text/javascript'>
window.onload = function() {
	console.log("DG", top.dg);
    if (window.opener) {
        window.close();
    } else {
        if (top.dg.isOpen() == true) {
            top.dg.closeFlow();
            return true;
        }
    }
};
</script>