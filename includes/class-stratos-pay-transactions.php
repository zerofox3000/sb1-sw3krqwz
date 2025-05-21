<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stratos Pay Transactions Class
 */
class Stratos_Pay_Transactions {
    /**
     * Table name
     */
    private static $table_name;
    
    /**
     * Initialize the class
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'stratos_pay_transactions';
    }
    
    /**
     * Create transactions table
     */
    public static function create_table() {
        global $wpdb;
        
        self::init();
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id varchar(100) NOT NULL,
            amount decimal(10,2) NOT NULL,
            currency varchar(3) NOT NULL,
            status varchar(20) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_name varchar(100) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_disputed tinyint(1) NOT NULL DEFAULT 0,
            dispute_reason varchar(255) DEFAULT NULL,
            dispute_status varchar(20) DEFAULT NULL,
            dispute_date datetime DEFAULT NULL,
            is_flagged tinyint(1) NOT NULL DEFAULT 0,
            metadata text,
            PRIMARY KEY  (id),
            KEY transaction_id (transaction_id),
            KEY status (status),
            KEY created_at (created_at),
            KEY is_disputed (is_disputed),
            KEY is_flagged (is_flagged)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get disputes with pagination
     */
    public static function get_disputes($per_page = 20, $page = 1) {
        global $wpdb;
        
        self::init();
        
        $offset = ($page - 1) * $per_page;
        
        $total = $wpdb->get_var(
            "SELECT COUNT(*) FROM " . self::$table_name . " WHERE is_disputed = 1"
        );
        
        $disputes = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_name . " 
                WHERE is_disputed = 1 
                ORDER BY dispute_date DESC 
                LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        return array(
            'total' => $total,
            'disputes' => $disputes
        );
    }
    
    /**
     * Add transaction
     */
    public static function add_transaction($data) {
        global $wpdb;
        
        self::init();
        
        return $wpdb->insert(
            self::$table_name,
            array(
                'transaction_id' => $data['transaction_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'status' => $data['status'],
                'customer_email' => $data['customer_email'],
                'customer_name' => $data['customer_name'],
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null
            ),
            array('%s', '%f', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get transaction by ID
     */
    public static function get_transaction($transaction_id) {
        global $wpdb;
        
        self::init();
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_name . " WHERE transaction_id = %s",
                $transaction_id
            )
        );
    }
    
    /**
     * Get transactions with pagination
     */
    public static function get_transactions($per_page = 20, $page = 1, $args = array()) {
        global $wpdb;
        
        self::init();
        
        $offset = ($page - 1) * $per_page;
        
        $where = "WHERE 1=1";
        $values = array();
        
        if (!empty($args['status'])) {
            $where .= " AND status = %s";
            $values[] = $args['status'];
        }
        
        if (!empty($args['search'])) {
            $where .= " AND (transaction_id LIKE %s OR customer_email LIKE %s OR customer_name LIKE %s)";
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }
        
        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM " . self::$table_name . " " . $where,
                $values
            )
        );
        
        $transactions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_name . " " . $where . " ORDER BY created_at DESC LIMIT %d OFFSET %d",
                array_merge($values, array($per_page, $offset))
            )
        );
        
        return array(
            'total' => $total,
            'transactions' => $transactions
        );
    }
    
    /**
     * Update transaction status
     */
    public static function update_status($transaction_id, $status) {
        global $wpdb;
        
        self::init();
        
        return $wpdb->update(
            self::$table_name,
            array('status' => $status),
            array('transaction_id' => $transaction_id),
            array('%s'),
            array('%s')
        );
    }
    
    /**
     * Flag transaction for investigation
     */
    public static function flag_transaction($transaction_id) {
        global $wpdb;
        
        self::init();
        
        return $wpdb->update(
            self::$table_name,
            array('is_flagged' => 1),
            array('transaction_id' => $transaction_id),
            array('%d'),
            array('%s')
        );
    }
}