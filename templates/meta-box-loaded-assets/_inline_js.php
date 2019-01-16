<?php
// no direct access
if (! isset($data)) {
	exit;
}
?>
<script type="text/javascript">
    var wpacuContentLinks           = document.getElementsByClassName('wpacu-assets-collapsible'),
        wpacuInlineCodeContentLinks = document.getElementsByClassName('wpacu-assets-inline-code-collapsible'),
        wpacuI, wpacuITwo;

    // "Styles" & "Scripts" main areas
    for (wpacuI = 0; wpacuI < wpacuContentLinks.length; wpacuI++) {
        wpacuContentLinks[wpacuI].addEventListener('click', function (e) {
            e.preventDefault();

            this.classList.toggle('wpacu-assets-collapsible-active');

            var assetsListContent = this.nextElementSibling;

            if (assetsListContent.style.maxHeight) {
                assetsListContent.style.maxHeight = null;
            } else {
                //assetsListContent.style.maxHeight = assetsListContent.scrollHeight + "px";
                assetsListContent.style.maxHeight = "100%";
            }
        });
    }

    // Inline code associated with the handle (expand)
    for (wpacuITwo = 0; wpacuITwo < wpacuInlineCodeContentLinks.length; wpacuITwo++) {
        wpacuInlineCodeContentLinks[wpacuITwo].addEventListener('click', function (e) {
            e.preventDefault();

            this.classList.toggle('wpacu-assets-inline-code-collapsible-active');

            var assetInlineCodeContent = this.nextElementSibling;

            if (assetInlineCodeContent.style.maxHeight) {
                assetInlineCodeContent.style.maxHeight = null;
            } else {
                assetInlineCodeContent.style.maxHeight = assetInlineCodeContent.scrollHeight + "px";
            }
        });
    }

    // Check if the contract / expand buttons exist (e.g. in view-default.php)
    var $wpacuContractAllBtn = document.getElementById('wpacu-assets-contract-all'),
        $wpacuExpandAllBtn = document.getElementById('wpacu-assets-expand-all');

    if (typeof($wpacuContractAllBtn) != 'undefined' && $wpacuContractAllBtn != null) {
        $wpacuContractAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            wpacuContractAllMainAreas();
        });
    }

    if (typeof($wpacuExpandAllBtn) != 'undefined' && $wpacuExpandAllBtn != null) {
        $wpacuExpandAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            wpacuExpandAllMainAreas();
        });
    }

    function wpacuExpandAllMainAreas() {
        var wpacuI, assetsListContent, wpacuContentLinks = document.getElementsByClassName('wpacu-assets-collapsible');

        for (wpacuI = 0; wpacuI < wpacuContentLinks.length; wpacuI++) {
            wpacuContentLinks[wpacuI].classList.add('wpacu-assets-collapsible-active');
            assetsListContent = wpacuContentLinks[wpacuI].nextElementSibling;
            //assetsListContent.style.maxHeight = assetsListContent.scrollHeight + 'px';
            assetsListContent.style.maxHeight = '100%';
            assetsListContent.classList.remove('wpacu-open');
        }
    }

    function wpacuContractAllMainAreas() {
        var wpacuI, assetsListContent, wpacuContentLinks = document.getElementsByClassName('wpacu-assets-collapsible');

        for (wpacuI = 0; wpacuI < wpacuContentLinks.length; wpacuI++) {
            wpacuContentLinks[wpacuI].classList.remove('wpacu-assets-collapsible-active');
            assetsListContent = wpacuContentLinks[wpacuI].nextElementSibling;
            assetsListContent.style.maxHeight = null;
        }
    }

    function wpacuExpandAllInlineCodeAreas()
    {
        var wpacuIE,
            assetInlineCodeContent,
            wpacuInlineCodeContentLinks = document.getElementsByClassName('wpacu-assets-inline-code-collapsible');

        for (wpacuIE = 0; wpacuIE < wpacuInlineCodeContentLinks.length; wpacuIE++) {
            wpacuInlineCodeContentLinks[wpacuIE].classList.add('wpacu-assets-inline-code-collapsible-active');
            assetInlineCodeContent = wpacuInlineCodeContentLinks[wpacuIE].nextElementSibling;
            assetInlineCodeContent.style.maxHeight = assetInlineCodeContent.scrollHeight + 'px';
            assetInlineCodeContent.classList.remove('wpacu-open');
        }
    }

    <?php
    // "Styles" and "Scripts"
    if ($data['plugin_settings']['assets_list_layout_areas_status'] === 'contracted') {
    ?>
        wpacuContractAllMainAreas();
    <?php
    } else {
    ?>
        // Remove 'wpacu-open' and set the right max-height to ensure the click action below will work smoothly
        wpacuExpandAllMainAreas();
    <?php
    }

    // "Inline code associated with the handle" - Expand all
    if ($data['plugin_settings']['assets_list_inline_code_status'] !== 'contracted') {
        ?>
            wpacuExpandAllInlineCodeAreas();
        <?php
    }
    ?>
</script>