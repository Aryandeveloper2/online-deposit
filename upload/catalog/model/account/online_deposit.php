<?php
class ModelAccountOnlineDeposit extends Model {

    public function addOnlineDeposit($data) {
        if(!isset($data['signature'])) {
            $data['signature'] = '';
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_online_deposit SET 
            customer_id = '" . (int)$data['customer_id'] . "', 
            status = '" . (int)$data['status'] . "', 
            payment_method = '" . $data['payment_method'] . "', 
            price = '" . (int)$data['price'] . "', 
            signature = '" . $this->db->escape($data['signature']) . "', 
            date_added = NOW()");

        return $this->db->getLastId();
    }

    public function editOnlineDeposit($customer_online_deposit_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer_online_deposit SET 
            customer_id = '" . (int)$data['customer_id'] . "', 
            status = '" . (int)$data['status'] . "', 
            payment_method = '" . $data['payment_method'] . "', 

            price = '" . (int)$data['price'] . "' 
            WHERE customer_online_deposit_id = '" . (int)$customer_online_deposit_id . "'");
    }
    
    public function editStatusOnlineDeposit($customer_online_deposit_id, $status) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer_online_deposit SET 
            status = '" . (int)$status . "'
            WHERE customer_online_deposit_id = '" . (int)$customer_online_deposit_id . "'");
    }

    public function deleteOnlineDeposit($customer_online_deposit_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_online_deposit WHERE customer_online_deposit_id = '" . (int)$customer_online_deposit_id . "'");
    }

    public function getOnlineDeposit($customer_online_deposit_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_online_deposit WHERE customer_online_deposit_id = '" . (int)$customer_online_deposit_id . "'");

        return $query->row;
    }

    public function getOnlineDeposits($data = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . "customer_online_deposit WHERE customer_id = " . $this->customer->getId();

        $sort_data = ['customer_id', 'status', 'price', 'date_added'];
        
        
        if(isset($data['filter_status'])) {
            if($data['filter_status'] == 2) {
                  $sql .= " AND status = 2";
            } else if($data['filter_status'] == 1) {
                  $sql .= " AND status = 1";
            }
        }
        
        if (!empty($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
        }

        if (!empty($data['order']) && $data['order'] == 'DESC') {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        // if (isset($data['start']) || isset($data['limit'])) {
        //     $start = (int)($data['start'] ?? 0);
        //     $limit = (int)($data['limit'] ?? 20);

        //     $sql .= " LIMIT " . $start . "," . $limit;
        // }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalOnlineDeposits() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_online_deposit");

        return $query->row['total'];
    }
}
