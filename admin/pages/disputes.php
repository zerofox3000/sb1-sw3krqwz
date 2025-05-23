<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle CSV export
if (isset($_POST['action']) && $_POST['action'] === 'export_disputes_csv') {
    check_admin_referer('export_disputes_csv');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="disputes-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, array(
        'Transaction ID',
        'Amount',
        'Currency',
        'Customer Name',
        'Customer Email',
        'Dispute Reason',
        'Status',
        'Date'
    ));
    
    // Get all disputes for export
    $all_disputes = Stratos_Pay_Transactions::get_disputes(9999, 1)['disputes'];
    
    foreach ($all_disputes as $dispute) {
        fputcsv($output, array(
            $dispute->transaction_id,
            $dispute->amount,
            $dispute->currency,
            $dispute->customer_name,
            $dispute->customer_email,
            $dispute->dispute_reason,
            $dispute->status,
            $dispute->created_at
        ));
    }
    
    fclose($output);
    exit;
}

// Get current page
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

// Get search parameters
$args = array();
if (!empty($_GET['status'])) {
    $args['status'] = sanitize_text_field($_GET['status']);
}
if (!empty($_GET['reason'])) {
    $args['reason'] = sanitize_text_field($_GET['reason']);
}

// Get disputes
$result = Stratos_Pay_Transactions::get_disputes($per_page, $page, $args);
$disputes = $result['disputes'];
$total = $result['total'];
$total_pages = ceil($total / $per_page);
?>

<div class="wrap stratos-pay-wrap">
    <h1 class="stratos-pay-title"><?php _e('Disputes', 'stratos-pay'); ?></h1>
    
    <div class="stratos-pay-container">
        <div class="stratos-pay-box">
            <!-- Search and Filter Form -->
            <div class="stratos-pay-filters">
                <form method="get" action="" class="stratos-pay-form-row">
                    <input type="hidden" name="page" value="stratos-pay-disputes">
                    
                    <div class="stratos-pay-filter-group">
                        <select name="status" class="stratos-pay-select">
                            <option value=""><?php _e('All Statuses', 'stratos-pay'); ?></option>
                            <option value="open" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'open'); ?>>
                                <?php _e('Open', 'stratos-pay'); ?>
                            </option>
                            <option value="under_review" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'under_review'); ?>>
                                <?php _e('Under Review', 'stratos-pay'); ?>
                            </option>
                            <option value="resolved" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'resolved'); ?>>
                                <?php _e('Resolved', 'stratos-pay'); ?>
                            </option>
                        </select>

                        <select name="reason" class="stratos-pay-select">
                            <option value=""><?php _e('All Reasons', 'stratos-pay'); ?></option>
                            <option value="fraudulent" <?php selected(isset($_GET['reason']) ? $_GET['reason'] : '', 'fraudulent'); ?>>
                                <?php _e('Fraudulent', 'stratos-pay'); ?>
                            </option>
                            <option value="duplicate" <?php selected(isset($_GET['reason']) ? $_GET['reason'] : '', 'duplicate'); ?>>
                                <?php _e('Duplicate', 'stratos-pay'); ?>
                            </option>
                            <option value="product_not_received" <?php selected(isset($_GET['reason']) ? $_GET['reason'] : '', 'product_not_received'); ?>>
                                <?php _e('Product Not Received', 'stratos-pay'); ?>
                            </option>
                        </select>
                        
                        <button type="submit" class="stratos-pay-button"><?php _e('Filter', 'stratos-pay'); ?></button>
                    </div>
                </form>

                <!-- Export Form -->
                <form method="post" action="" class="stratos-pay-export-form">
                    <?php wp_nonce_field('export_disputes_csv'); ?>
                    <input type="hidden" name="action" value="export_disputes_csv">
                    <button type="submit" class="stratos-pay-button">
                        <?php _e('Export to CSV', 'stratos-pay'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Disputes Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Transaction ID', 'stratos-pay'); ?></th>
                        <th><?php _e('Amount', 'stratos-pay'); ?></th>
                        <th><?php _e('Customer', 'stratos-pay'); ?></th>
                        <th><?php _e('Dispute Reason', 'stratos-pay'); ?></th>
                        <th><?php _e('Status', 'stratos-pay'); ?></th>
                        <th><?php _e('Date', 'stratos-pay'); ?></th>
                        <th><?php _e('Actions', 'stratos-pay'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($disputes)) : ?>
                        <?php foreach ($disputes as $dispute) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($dispute->transaction_id); ?></strong>
                                </td>
                                <td>
                                    <?php echo esc_html(Stratos_Pay_Helper::format_currency($dispute->amount, $dispute->currency)); ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($dispute->customer_name); ?></strong><br>
                                    <small><?php echo esc_html($dispute->customer_email); ?></small>
                                </td>
                                <td>
                                    <?php echo esc_html($dispute->dispute_reason); ?>
                                </td>
                                <td>
                                    <span class="stratos-pay-status stratos-pay-status-<?php echo esc_attr($dispute->status); ?>">
                                        <?php echo esc_html(ucfirst($dispute->status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($dispute->created_at))); ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=stratos-pay-disputes&action=view&id=' . $dispute->id)); ?>" 
                                       class="stratos-pay-button">
                                        <?php _e('View Details', 'stratos-pay'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7"><?php _e('No disputes found.', 'stratos-pay'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $page
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>