<?php
// no direct access
if (! isset($data)) {
	exit;
}
?>
<script type="text/javascript">
    var wpacuContentLinks = document.getElementsByClassName('wpacu-assets-collapsible'), wpacuI;

    for (wpacuI = 0; wpacuI < wpacuContentLinks.length; wpacuI++) {
        wpacuContentLinks[wpacuI].addEventListener('click', function (e) {
            e.preventDefault();

            this.classList.toggle('wpacu-assets-collapsible-active');

            var assetsListContent = this.nextElementSibling;

            if (assetsListContent.style.maxHeight) {
                assetsListContent.style.maxHeight = null;
            } else {
                assetsListContent.style.maxHeight = assetsListContent.scrollHeight + "px";
            }
        });
    }

    document.getElementById('wpacu-assets-contract-all').addEventListener('click', function (e) {
        e.preventDefault();
        wpacuContractAll();
    });

    document.getElementById('wpacu-assets-expand-all').addEventListener('click', function (e) {
        e.preventDefault();
        wpacuExpandAll();
    });

    function wpacuExpandAll() {
        var wpacuI, assetsListContent, wpacuContentLinks = document.getElementsByClassName('wpacu-assets-collapsible');

        for (wpacuI = 0; wpacuI < wpacuContentLinks.length; wpacuI++) {
            wpacuContentLinks[wpacuI].classList.add('wpacu-assets-collapsible-active');
            assetsListContent = wpacuContentLinks[wpacuI].nextElementSibling;
            assetsListContent.style.maxHeight = assetsListContent.scrollHeight + 'px';
            assetsListContent.classList.remove('wpacu-open');
        }
    }

    function wpacuContractAll() {
        var wpacuI, assetsListContent, wpacuContentLinks = document.getElementsByClassName('wpacu-assets-collapsible');

        for (wpacuI = 0; wpacuI < wpacuContentLinks.length; wpacuI++) {
            wpacuContentLinks[wpacuI].classList.remove('wpacu-assets-collapsible-active');
            assetsListContent = wpacuContentLinks[wpacuI].nextElementSibling;
            assetsListContent.style.maxHeight = null;
        }
    }

    <?php
    if ($data['plugin_settings']['assets_list_layout_areas_status'] === 'contracted') {
    ?>
        wpacuContractAll();
    <?php
    } else {
    ?>
        // Remove 'wpacu-open' and set the right max-height to ensure the click action below will work smoothly
        wpacuExpandAll();
    <?php
    }
    ?>
</script>