<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current page
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

// Get disputes
$result = Stratos_Pay_Transactions::get_disputes($per_page, $page);
$disputes = $result['disputes'];
$total = $result['total'];
$total_pages = ceil($total / $per_page);
?>

<div class="wrap stratos-pay-wrap">
    <h1><?php _e('Disputes', 'stratos-pay'); ?></h1>
    
    <div class="stratos-pay-container">
        <div class="stratos-pay-disputes">
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
                                       class="button button-small">
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