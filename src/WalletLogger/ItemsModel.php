<?php

namespace WalletLogger;

use Illuminate\Database\Capsule\Manager;
use WalletLogger\Interfaces\ItemsModelInterface;

class ItemsModel implements ItemsModelInterface
{
    protected $table;

    protected $table_name;

    public $mandatory_fields = [];

    public $optional_fields = ['deleted_at'];

    public function __construct(Manager $db)
    {
        $this->table = $db::table($this->table_name);
    }

    public function getList(array $filters, $order_by = 'id', $desc = true)
    {
        if ($desc) {
            $results = $this->table
                ->orderByDesc($order_by)
                ->get();
        } else {
            $results = $this->table
                ->orderBy($order_by)
                ->get();
        }

        foreach ($filters as $filter => $value) {
            if (in_array($filter, $this->mandatory_fields, false) || in_array($filter, $this->optional_fields, false)) {
                $results = $results->where($filter, $value);
            }
        }

        return $results;
    }

    public function getItem($id)
    {
        return $this->table
            ->find($id);
    }

    public function createItem($item_data)
    {
        $parsed_fields = [];
        // Check if there are all mandatory fields
        foreach ($this->mandatory_fields as $field) {
            if (!isset($item_data[$field])) {
                throw new \InvalidArgumentException('Mandatory field `' . $field . '` is missing', 500);
            }
            $parsed_fields[$field] = $item_data[$field];
        }

        // Clear passed array to leave only mandatory and optional fields
        foreach ($this->optional_fields as $field) {
            if (isset($item_data[$field])) {
                $parsed_fields[$field] = $item_data[$field];
            }
        }

        return $this->table->insertGetId($parsed_fields);
    }

    public function updateItem($item_id, Array $updated_data)
    {
        $item = $this->table->find((int)$item_id);
        if ($item) {
            $parsed_fields = [];
            $available_fields = array_merge($this->mandatory_fields, $this->optional_fields);

            // Clear passed array to leave only mandatory and optional fields
            foreach ($available_fields as $field) {
                if (isset($updated_data[$field])) {
                    $parsed_fields[$field] = $updated_data[$field];
                }
            }

            return $this->table->update($parsed_fields);
        }

        return false;
    }
}