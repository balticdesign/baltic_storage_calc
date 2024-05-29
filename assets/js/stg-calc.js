
function updateSummary() {
    var summary = document.getElementById('itemSummary');
    summary.innerHTML = ''; // Clear current summary table body

    var totalVolume = 0;
    var roomTotals = {}; // Object to store total items per room
    var inputs = document.querySelectorAll('input[type=number]'); // Define inputs here

    inputs.forEach(function(input) {
        var value = parseInt(input.value, 10);
        var room = input.getAttribute('data-room');

        // Initialize room total in the object if not already
        if (!roomTotals[room]) {
            roomTotals[room] = 0;
        }

        if (value > 0) {

            roomTotals[room] += value;
            var volume = parseFloat(input.dataset.volume);
            var itemVolume = volume * value; // Calculate volume for this item
            totalVolume += itemVolume; // Add to total volume
    
            var label = document.querySelector('label[for="' + input.id + '"]');
            var title = label ? label.innerText : '';
    
            var row = summary.insertRow();
            row.insertCell(0).textContent = title;
            
            var quantityCell = row.insertCell(1);
            quantityCell.textContent = value;
            quantityCell.className = 'center-text'; // Add class to quantity cell
    
            var volumeCell = row.insertCell(2);
            volumeCell.textContent = itemVolume.toFixed(2);
            volumeCell.className = 'center-text'; // Add class to volume cell
        }
    });

    // Update accordion labels and classes based on room totals
    for (var roomSlug in roomTotals) {
        var totalItems = roomTotals[roomSlug];
        var accordionLabel = document.querySelector('label[for="accordion-' + roomSlug + '"]');

        if (totalItems > 0) {
            accordionLabel.classList.add('has-items'); // Add a class
            accordionLabel.textContent = accordionLabel.textContent.split(' (')[0] + ' (' + totalItems + ' items)'; // Update label text
        } else {
            accordionLabel.classList.remove('has-items'); // Remove the class if no items
            accordionLabel.textContent = accordionLabel.textContent.split(' (')[0]; // Reset label text
        }
    }

    // Update total volume in the table footer
    document.getElementById('totalVolume').textContent = totalVolume.toFixed(2);
    document.getElementById('vol').textContent = totalVolume.toFixed(2);
    var summaryHtml = document.getElementById('summaryTable').outerHTML;
    jQuery('#input_5_4').val(summaryHtml);

    // For testing
    //alert(summaryHtml);
}
jQuery(document).ready(function() {

    var $summaryList = jQuery('#summaryList');
    var $toggleButton = jQuery('#invTog');
    var $closeButton = jQuery('#close');
    var isPanelOpen = false;

    function adjustPanelPosition() {
        var screenWidth = jQuery(window).width();
        var panelWidth = $summaryList.outerWidth();

        if (isPanelOpen) {
            $summaryList.css('right', '0');
        } else {
            $summaryList.css('right', '-' + panelWidth + 'px');
        }
    }

    function togglePanel() {
        isPanelOpen = !isPanelOpen;
        adjustPanelPosition();
    }

    jQuery('.room-toggle').change(function() {
        var room = jQuery(this).data('room');
        var isChecked = jQuery(this).is(':checked');
    
        // Toggle the label display
        if (isChecked) {
            jQuery('label[for="accordion-' + room + '"]').css('display', 'block');
        } else {
            jQuery('label[for="accordion-' + room + '"]').css('display', 'none');
        }
    });

    $toggleButton.on('click', togglePanel);
    $closeButton.on('click', togglePanel);
    
    // Adjust position on window resize
    jQuery(window).on('resize', function() {
        if (isPanelOpen) {
            adjustPanelPosition();
        }
    });

    // Initial adjustment
    adjustPanelPosition();

jQuery('#resetButton').on('click', function() {
    // Reset the form
    jQuery('#gform_5')[0].reset();

    jQuery('#vol').text('0');

    // Clear the table
    jQuery('#itemSummary').empty();

    // Reset total volume in the table footer
    jQuery('#totalVolume').text('');

    jQuery('.accordion-label').each(function() {
        jQuery(this).removeClass('has-items');
        // Reset the label text to its original state
        var originalText = jQuery(this).text().split(' (')[0];
        jQuery(this).text(originalText);
    });
});
});

function incrementValue(id) {
    var input = document.getElementById(id);
    var value = parseInt(input.value, 10);
    value = isNaN(value) ? 0 : value;
    value++;
    input.value = value;
    updateSummary(); // Call updateSummary whenever the value changes
}

function decrementValue(id) {
    var input = document.getElementById(id);
    var value = parseInt(input.value, 10);
    value = isNaN(value) ? 0 : value;
    value < 1 ? value = 0 : value--;
    input.value = value;
    updateSummary(); // Call updateSummary whenever the value changes
}