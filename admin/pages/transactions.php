<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle flag action
if (isset($_POST['action']) && $_POST['action'] === 'flag_transaction' && isset($_POST['transaction_id'])) {
    check_admin_referer('flag_transaction_' . $_POST['transaction_id']);
    
    $transaction_id = sanitize_text_field($_POST['transaction_id']);
    Stratos_Pay_Helper::flag_transaction($transaction_id);
    
    add_settings_error(
        'stratos_pay_messages',
        'transaction_flagged',
        __('Transaction has been flagged for investigation.', 'stratos-pay'),
        'updated'
    );
}

// Handle CSV export
if (isset($_POST['action']) && $_POST['action'] === 'export_csv') {
    check_admin_referer('export_transactions_csv');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, array(
        'Transaction ID',
        'Amount',
        'Currency',
        'Customer Name',
        'Customer Email',
        'Status',
        'Date'
    ));
    
    // Get all transactions for export
    $all_transactions = Stratos_Pay_Transactions::get_transactions(9999, 1)['transactions'];
    
    foreach ($all_transactions as $transaction) {
        fputcsv($output, array(
            $transaction->transaction_id,
            $transaction->amount,
            $transaction->currency,
            $transaction->customer_name,
            $transaction->customer_email,
            $transaction->status,
            $transaction->created_at
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
if (!empty($_GET['search'])) {
    $args['search'] = sanitize_text_field($_GET['search']);
}
if (!empty($_GET['type'])) {
    $args['type'] = sanitize_text_field($_GET['type']);
}

// Get transactions
$result = Stratos_Pay_Transactions::get_transactions($per_page, $page, $args);
$transactions = $result['transactions'];
$total = $result['total'];
$total_pages = ceil($total / $per_page);

// Display any error messages
settings_errors('stratos_pay_messages');
?>

<div class="wrap stratos-pay-wrap">
    <h1 class="stratos-pay-title"><?php _e('Transactions', 'stratos-pay'); ?></h1>
    
    <div class="stratos-pay-container">
        <div class="stratos-pay-box">
            <!-- Search and Filter Form -->
            <div class="stratos-pay-filters">
                <form method="get" action="" class="stratos-pay-form-row">
                    <input type="hidden" name="page" value="stratos-pay-transactions">
                    
                    <div class="stratos-pay-filter-group">
                        <select name="status" class="stratos-pay-select">
                            <option value=""><?php _e('All Statuses', 'stratos-pay'); ?></option>
                            <option value="completed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'completed'); ?>>
                                <?php _e('Completed', 'stratos-pay'); ?>
                            </option>
                            <option value="pending" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'pending'); ?>>
                                <?php _e('Pending', 'stratos-pay'); ?>
                            </option>
                            <option value="failed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'failed'); ?>>
                                <?php _e('Failed', 'stratos-pay'); ?>
                            </option>
                        </select>

                        <select name="type" class="stratos-pay-select">
                            <option value=""><?php _e('All Types', 'stratos-pay'); ?></option>
                            <option value="card" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'card'); ?>>
                                <?php _e('Card Payment', 'stratos-pay'); ?>
                            </option>
                            <option value="bank" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'bank'); ?>>
                                <?php _e('Bank Transfer', 'stratos-pay'); ?>
                            </option>
                            <option value="crypto" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'crypto'); ?>>
                                <?php _e('Cryptocurrency', 'stratos-pay'); ?>
                            </option>
                        </select>
                        
                        <input type="search" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>" 
                               placeholder="<?php _e('Search transactions...', 'stratos-pay'); ?>"
                               class="stratos-pay-search">
                        
                        <button type="submit" class="stratos-pay-button"><?php _e('Filter', 'stratos-pay'); ?></button>
                    </div>
                </form>

                <!-- Export Form -->
                <form method="post" action="" class="stratos-pay-export-form">
                    <?php wp_nonce_field('export_transactions_csv'); ?>
                    <input type="hidden" name="action" value="export_csv">
                    <button type="submit" class="stratos-pay-button">
                        <?php _e('Export to CSV', 'stratos-pay'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Transactions Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Transaction ID', 'stratos-pay'); ?></th>
                        <th><?php _e('Amount', 'stratos-pay'); ?></th>
                        <th><?php _e('Customer', 'stratos-pay'); ?></th>
                        <th><?php _e('Type', 'stratos-pay'); ?></th>
                        <th><?php _e('Status', 'stratos-pay'); ?></th>
                        <th><?php _e('Date', 'stratos-pay'); ?></th>
                        <th><?php _e('Actions', 'stratos-pay'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)) : ?>
                        <?php foreach ($transactions as $transaction) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($transaction->transaction_id); ?></strong>
                                </td>
                                <td>
                                    <?php echo esc_html(Stratos_Pay_Helper::format_currency($transaction->amount, $transaction->currency)); ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($transaction->customer_name); ?></strong><br>
                                    <small><?php echo esc_html($transaction->customer_email); ?></small>
                                </td>
                                <td>
                                    <?php echo esc_html(ucfirst($transaction->type)); ?>
                                </td>
                                <td>
                                    <span class="stratos-pay-status stratos-pay-status-<?php echo esc_attr($transaction->status); ?>">
                                        <?php echo esc_html(ucfirst($transaction->status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($transaction->created_at))); ?>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('flag_transaction_' . $transaction->transaction_id); ?>
                                        <input type="hidden" name="action" value="flag_transaction">
                                        <input type="hidden" name="transaction_id" value="<?php echo esc_attr($transaction->transaction_id); ?>">
                                        <button type="submit" class="stratos-pay-button">
                                            <?php _e('Flag', 'stratos-pay'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7"><?php _e('No transactions found.', 'stratos-pay'); ?></td>
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