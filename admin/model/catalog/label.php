<?php
class ModelCatalogLabel extends Model {
	
	public function addLabel($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "label SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$label_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "label SET image = '" . $this->db->escape($data['image']) . "' WHERE label_id = '" . (int)$label_id . "'");
		}

		if (isset($data['label_store'])) {
			foreach ($data['label_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "label_to_store SET label_id = '" . (int)$label_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'label_id=" . (int)$label_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('label');

		return $label_id;
	}

	public function editLabel($label_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "label SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE label_id = '" . (int)$label_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "label SET image = '" . $this->db->escape($data['image']) . "' WHERE label_id = '" . (int)$label_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "label_to_store WHERE label_id = '" . (int)$label_id . "'");

		if (isset($data['label_store'])) {
			foreach ($data['label_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "label_to_store SET label_id = '" . (int)$label_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'label_id=" . (int)$label_id . "'");

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'label_id=" . (int)$label_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('label');
	}

	public function deleteLabel($label_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "label WHERE label_id = '" . (int)$label_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "label_to_store WHERE label_id = '" . (int)$label_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'label_id=" . (int)$label_id . "'");

		$this->cache->delete('label');
	}

	public function getLabel($label_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'label_id=" . (int)$label_id . "') AS keyword FROM " . DB_PREFIX . "label WHERE label_id = '" . (int)$label_id . "'");

		return $query->row;
	}

	public function getLabels($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "label";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

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
	}

	public function getLabelStores($label_id) {
		$label_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "label_to_store WHERE label_id = '" . (int)$label_id . "'");

		foreach ($query->rows as $result) {
			$label_store_data[] = $result['store_id'];
		}

		return $label_store_data;
	}

	public function getTotalLabels() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "label");

		return $query->row['total'];
	}
}
