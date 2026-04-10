<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Create table `icash_tabelas`
if (!$CI->db->table_exists(db_prefix() . 'icash_tabelas')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'icash_tabelas` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `nome_tabela` VARCHAR(255) NOT NULL,
        `parcelas` TEXT DEFAULT NULL,
        `empresa` VARCHAR(255) DEFAULT NULL,
        `credenciadora` VARCHAR(255) NOT NULL,
        `loja` VARCHAR(255) DEFAULT NULL,
        `representante` VARCHAR(255) DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        `date_updated` DATETIME NOT NULL,
        `operator_id` INT(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// Create table `icash_credenciadoras`
if (!$CI->db->table_exists(db_prefix() . 'icash_credenciadoras')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'icash_credenciadoras` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `period` VARCHAR(20) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

/**
 * CRIANDO NOVAS COLUNAS NA TABELA STAFF
 */

$columnsArray = [
    "cpf_cnpj"              => "TINYTEXT DEFAULT NULL",
    "comp_endereco"         => "TINYTEXT DEFAULT NULL",
    "contrato_social"       => "TINYTEXT DEFAULT NULL",
    "doc_socio_principal"   => "TINYTEXT DEFAULT NULL",
    "foto_fachada"          => "TINYTEXT DEFAULT NULL",
    "gerente_id"            => "INT(11) DEFAULT NULL",
    "supervisor_id"         => "INT(11) DEFAULT NULL",
    "unidade_id"            => "INT(11) DEFAULT NULL",
    "loja_id"               => "INT(11) DEFAULT NULL",
    "contrato_id"           => "INT(11) DEFAULT NULL",
    "short_link"            => "TINYTEXT DEFAULT NULL",
    "perfex_saas_tenant_id" => "TINYTEXT DEFAULT NULL"
];

foreach ($columnsArray as $col => $config) {
    if (!$CI->db->field_exists($col, db_prefix() . 'staff')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
            ADD COLUMN `{$col}` {$config};
        ");
    }
}


$columnsArray = [
    "short_link"                => "TINYTEXT DEFAULT NULL",
    "contract_id"               => "INT(11) DEFAULT NULL",
    "rg_frente"                 => "TINYTEXT DEFAULT NULL",
    "rg_verso"                  => "TINYTEXT DEFAULT NULL",
    "cartao_frente"             => "TINYTEXT DEFAULT NULL",
    "cartao_verso"              => "TINYTEXT DEFAULT NULL",
    "selfie_identidade"         => "TINYTEXT DEFAULT NULL",
    "extra_file"                => "TINYTEXT DEFAULT NULL",
    "addedfrom"                 => "INT(11) DEFAULT NULL",
    "atendente_id"              => "INT(11) DEFAULT NULL",
    "payment_link"              => "TINYTEXT DEFAULT NULL",
    "payment_details"           => "TEXT DEFAULT NULL",
    "checkout_order_number"     => "TEXT DEFAULT NULL",
    "payment_maskedcreditcard"  => "TEXT DEFAULT NULL",
    "brand"                     => "VARCHAR(255) DEFAULT NULL",
    "bank_message"              => "VARCHAR(255) DEFAULT NULL",
    "link_id"                   => "TEXT DEFAULT NULL",
    "nsu"                       => "INT(11) DEFAULT NULL",
    "payment_status"            => "INT(11) DEFAULT NULL",
    "payment_message"           => "TEXT DEFAULT NULL",
    "payment_description"       => "TEXT DEFAULT NULL",
    "payment_date"              => "DATETIME DEFAULT NULL",
    "payment_from_manager"      => "DATETIME DEFAULT NULL",
    "proposal_refusal"          => "TEXT DEFAULT NULL",
    "proposal_observation"      => "TEXT DEFAULT NULL",
    "customer_identity"         => "TEXT DEFAULT NULL",
    "customer_name"             => "VARCHAR(255) DEFAULT NULL",
    "customer_phone"            => "VARCHAR(255) DEFAULT NULL",
    "customer_email"            => "VARCHAR(255) DEFAULT NULL",
    "update_date"               => "DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    "update_at"                 => "DATETIME DEFAULT CURRENT_TIMESTAMP",
    "etapa"                     => "TEXT DEFAULT NULL",
    "sign_link"                 => "VARCHAR(255) DEFAULT NULL",
    "sign_id"                   => "VARCHAR(255) DEFAULT NULL",
    "document"                  => "VARCHAR(255) DEFAULT NULL",

];

foreach ($columnsArray as $col => $config) {
    if (!$CI->db->field_exists($col, db_prefix() . 'proposals')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . "proposals`
            ADD COLUMN `{$col}` {$config};
        ");
    }
}



$columnsArray = [
    "proposal_id" => "INT(11) DEFAULT NULL"
];

foreach ($columnsArray as $col => $config) {
    if (!$CI->db->field_exists($col, db_prefix() . 'contracts')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . "contracts`
            ADD COLUMN `{$col}` {$config};
        ");
    }
}

foreach ($columnsArray as $col => $config) {
    if (!$CI->db->field_exists($col, db_prefix() . 'invoices')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . "invoices`
            ADD COLUMN `{$col}` {$config};
        ");
    }
}


/**
 * CRIA UM TRIGGER PARA ATUALIZAR O UPDATE DATE DE PROPOSALS
 */

// Remove triggers existentes, se houver
$CI->db->query("DROP TRIGGER IF EXISTS trigger_update_proposal_on_customfield_update");
$CI->db->query("DROP TRIGGER IF EXISTS trigger_update_proposal_on_customfield_insert");

// Trigger para UPDATE em custom fields (aplica o NEW.value na coluna 'etapa' e atualiza 'update_at')
$CI->db->query("
    CREATE TRIGGER trigger_update_proposal_on_customfield_update
    AFTER UPDATE ON `" . db_prefix() . "customfieldsvalues`
    FOR EACH ROW
    BEGIN
        IF NEW.fieldto = 'proposal' AND NEW.fieldid = 64 AND NEW.value <> OLD.value THEN
            UPDATE `" . db_prefix() . "proposals`
            SET 
                etapa = NEW.value,
                update_at = CURRENT_TIMESTAMP
            WHERE id = NEW.relid;
        END IF;
    END
");

// Trigger para INSERT em custom fields (também insere NEW.value na 'etapa' e atualiza 'update_at')
$CI->db->query("
    CREATE TRIGGER trigger_update_proposal_on_customfield_insert
    AFTER INSERT ON `" . db_prefix() . "customfieldsvalues`
    FOR EACH ROW
    BEGIN
        IF NEW.fieldto = 'proposal' AND NEW.fieldid = 64 THEN
            UPDATE `" . db_prefix() . "proposals`
            SET 
                etapa = NEW.value,
                update_at = CURRENT_TIMESTAMP
            WHERE id = NEW.relid;
        END IF;
    END
");


// Create table `icash_history`
if (!$CI->db->table_exists(db_prefix() . 'icash_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'icash_history` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modulo` VARCHAR(255) DEFAULT NULL,
        `etapa` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(255) DEFAULT NULL,
        `observacao` TEXT DEFAULT NULL,
        `link` TEXT DEFAULT NULL,
        `staff_id` INT(11) DEFAULT NULL,
        `user_id` INT(11) DEFAULT NULL,
        `unit_id` INT(11) DEFAULT NULL,
        `id_registro` INT(11) DEFAULT NULL,
        `historico` TEXT DEFAULT NULL,
        `method` TEXT DEFAULT NULL,
        `version` VARCHAR(15) DEFAULT NULL,
        `date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}
