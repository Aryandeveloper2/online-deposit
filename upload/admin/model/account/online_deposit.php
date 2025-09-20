<?php
class ModelExtensionModuleOnlineDeposit extends Model {

    public function addOnlineDeposit($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_online_deposit SET 
            customer_id = '" . (int)$data['customer_id'] . "', 
            status = '" . (int)$data['status'] . "', 
            payment_method = '" . $data['payment_method'] . "', 
            price = '" . (int)$data['price'] . "', 
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
        $sql = "SELECT cod.signature, cod.date_added,cod.status, cod.price, cod.customer_id, CONCAT(c.firstname, ' ' , c.lastname) as fullname, cod.payment_method FROM " . DB_PREFIX . "customer_online_deposit cod JOIN " . DB_PREFIX . "customer c ON (cod.customer_id = c.customer_id) ";

        $sort_data = ['customer_id', 'status', 'price', 'date_added'];

        if (!empty($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY cod." . $data['sort'];
        } else {
            $sql .= " ORDER BY cod.date_added";
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
