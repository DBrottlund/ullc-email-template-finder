jQuery(document).ready(function($) {
    var scanButton = $('#ullc-etf-scan-button');
    var resultsDiv = $('.ullc-etf-results');
    var progressDiv = $('<div class="ullc-etf-progress"></div>').insertAfter(scanButton);
    var spinner = $('<span class="spinner"></span>').insertAfter(scanButton);

    function initializeScan() {
        scanButton.prop('disabled', true);
        spinner.addClass('active');
        progressDiv.addClass('active').html('Initializing scan...');
        resultsDiv.empty();
    }

    function updateProgress(message) {
        progressDiv.html(message);
    }

    function displayError(message) {
        $('<div class="ullc-etf-error"></div>')
            .text(message)
            .insertBefore(resultsDiv);
        scanButton.prop('disabled', false);
        spinner.removeClass('active');
    }

    function displayResults(templates) {
        if (!templates.length) {
            resultsDiv.html('<div class="ullc-etf-success">No email templates found.</div>');
            return;
        }

        var table = $('<table></table>');
        var header = $('<tr></tr>')
            .append('<th>Type</th>')
            .append('<th>Location</th>')
            .append('<th>File</th>')
            .append('<th>Send Line</th>')
            .append('<th>Template Line</th>')
            .append('<th>Trigger</th>');
        
        table.append(header);

        templates.forEach(function(template) {
            var row = $('<tr></tr>');
            row.append('<td><span class="ullc-etf-badge ullc-etf-badge-' + template.type + '">' + template.type + '</span></td>');
            row.append('<td>' + template.location + '</td>');
            row.append('<td><span class="ullc-etf-file-path">' + template.file + '</span></td>');
            row.append('<td><span class="ullc-etf-line-number">' + template.send_line_number + '</span></td>');
            row.append('<td><span class="ullc-etf-line-number">' + template.template_line_number + '</span></td>');
            row.append('<td>' + template.trigger + '</td>');
            table.append(row);
        });

        resultsDiv.html(table);
    }

    function performScan() {
        // Start the scanning process
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ullc_etf_scan',
                nonce: ullc_etf.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateProgress('Scan complete!');
                    displayResults(response.data.templates);
                } else {
                    displayError(response.data.message || 'An error occurred during the scan.');
                }
            },
            error: function() {
                displayError('Failed to communicate with the server.');
            },
            complete: function() {
                scanButton.prop('disabled', false);
                spinner.removeClass('active');
                progressDiv.removeClass('active');
            }
        });
    }

    // Bind click event to scan button
    scanButton.on('click', function(e) {
        e.preventDefault();
        initializeScan();
        performScan();
    });
});