<?php
class ModelCataloglabel extends Model {
	public function getLabel($label_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "label m LEFT JOIN " . DB_PREFIX . "label_to_store m2s ON (m.label_id = m2s.label_id) WHERE m.label_id = '" . (int)$label_id . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}

	public function getLabels($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "label m LEFT JOIN " . DB_PREFIX . "label_to_store m2s ON (m.label_id = m2s.label_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

			$sort_data = array(
				'name',
				'sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY name";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$label_data = $this->cache->get('label.' . (int)$this->config->get('config_store_id'));

			if (!$label_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "label m LEFT JOIN " . DB_PREFIX . "label_to_store m2s ON (m.label_id = m2s.label_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY name");

				$label_data = $query->rows;

				$this->cache->set('label.' . (int)$this->config->get('config_store_id'), $label_data);
			}

			return $label_data;
		}
	}
}
