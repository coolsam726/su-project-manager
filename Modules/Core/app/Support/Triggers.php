<?php

namespace Modules\Core\Support;

class Triggers
{
    public function immutableUpdateTrigger(string $table, bool $drop = false): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // If the table doesn't have the record_status column or is_immutable, return
        if (! array_intersect(core()->getColumns($table), ['record_status', 'is_immutable'])) {
            return null;
        }
        $name = $table.'_immutable_update_trigger';
        if ($drop) {
            return "DROP TRIGGER IF EXISTS `$name`";
        }
        $old = $this->makeOldJsonObject($table);

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` BEFORE UPDATE ON `$table` FOR EACH ROW
                BEGIN
                    SET @old = $old
                    SET @recordStatus  = JSON_UNQUOTE(JSON_EXTRACT(@old, '$.record_status'));
                    SET @isImmutable = JSON_UNQUOTE(JSON_EXTRACT(@old, '$.is_immutable'));
                    -- if the table has a record_status column, ensure the only allowed status is draft
                    IF @recordStatus = 'final' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'You cannot update the record in $table since it is final';
                    END IF;
                    -- if the table has is_immutable column, check if is_immutable is true and throw an error
                    IF @isImmutable = 1 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'You cannot update the record $table since it is immutable';
                    END IF;
                END
            SQL;
    }

    public function immutableDeleteTrigger(string $table, bool $drop = false): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        if (! array_intersect(core()->getColumns($table), ['record_status', 'is_immutable'])) {
            return null;
        }
        $name = $table.'_immutable_delete_trigger';
        if ($drop) {
            return "DROP TRIGGER IF EXISTS `$name`";
        }

        $old = $this->makeOldJsonObject($table);

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` BEFORE DELETE ON `$table` FOR EACH ROW
                BEGIN
                    SET @old = $old
                    SET @recordStatus  = JSON_UNQUOTE(JSON_EXTRACT(@old, '$.record_status'));
                    SET @isImmutable = JSON_UNQUOTE(JSON_EXTRACT(@old, '$.is_immutable'));
                    -- if the table has a record_status column, ensure the only allowed status is draft
                    IF @recordStatus = 'closed' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'You cannot delete the record in $table since it is closed';
                    END IF;
                    -- if the table has is_immutable column, check if is_immutable is true and throw an error
                    IF @isImmutable = 1 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'You cannot delete the record $table since it is immutable';
                    END IF;
                END
            SQL;
    }

    public function usersAutoCreateProfile(bool $drop = false): ?string
    {
        // return if the current driver is not mysql or mariadb
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        $name = 'new_users_auto_create_profile_trigger';
        if ($drop) {
            return "DROP TRIGGER IF EXISTS `$name`";
        }

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` AFTER INSERT ON `users` FOR EACH ROW
                BEGIN
                    SET @name = NEW.name;
                    SET @first_name = SUBSTRING_INDEX(@name, ' ', 1);
                    SET @last_name = SUBSTRING_INDEX(@name, ' ', -1);
                    
                    IF NEW.is_immutable = 0 THEN
                        INSERT INTO user_profiles (user_id, first_name, last_name, email, created_at, updated_at)
                        VALUES (NEW.id, @first_name, @last_name, NEW.email, NOW(), NOW());
                    END IF;
                END
            SQL;
    }

    public function usersUpdateOrCreateCreateProfile(bool $drop = false): ?string
    {
        // return if the current driver is not mysql or mariadb
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        $name = 'users_update_or_create_profile_trigger';
        if ($drop) {
            return "DROP TRIGGER IF EXISTS `$name`";
        }

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` AFTER UPDATE ON `users` FOR EACH ROW
                BEGIN
                    SET @name = NEW.name;
                    SET @first_name = SUBSTRING_INDEX(@name, ' ', 1);
                    SET @last_name = SUBSTRING_INDEX(@name, ' ', -1);
                    -- If immutable return without creating the profile
                    IF NEW.is_immutable = 0 THEN
                        IF NOT EXISTS (SELECT * FROM user_profiles WHERE user_id = NEW.id) THEN
                            INSERT INTO user_profiles (user_id, first_name, last_name, email, created_at, updated_at)
                            VALUES (NEW.id, @first_name, @last_name, NEW.email, NOW(), NOW());
                        ELSE
                            UPDATE user_profiles SET first_name = @first_name, last_name = @last_name, email = NEW.email, updated_at = NOW() WHERE user_id = NEW.id;
                        END IF;
                    END IF;
                END
            SQL;
    }

    public function parentCheckBeforeUpdateTrigger(string $table, string $parent_table, string $fk = 'parent_id', bool $drop = false): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // check if the table has that column
        if (! in_array($fk, core()->getColumns($table))) {
            return null;
        }
        $name = $table.'_parent_status_before_update_check_trigger';
        if ($drop) {
            return "DROP TRIGGER IF EXISTS `$name`";
        }

        $parent = $this->makeRecordJsonObject($parent_table, '@fk');

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` BEFORE UPDATE ON `$table` FOR EACH ROW
                BEGIN
                    SET @fk = OLD.$fk;
                    SET @parent = $parent
                    SET @parentStatus = JSON_UNQUOTE(JSON_EXTRACT(@parent, '$.record_status'));
                    SET @isImmutable = JSON_UNQUOTE(JSON_EXTRACT(@parent, '$.is_immutable'));
                    
                    IF @parentStatus = 'closed' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot update $table whose parent ($fk) is closed';
                    ELSEIF @parentStatus = 'cancelled' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot update $table whose parent ($fk) is cancelled';
                    ELSEIF @parentStatus = 'posted' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot update $table whose parent ($fk) is posted';
                    END IF;
                    
                    IF @isImmutable = 1 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot update $table whose parent ($fk) is immutable';
                    END IF;
                    
                END
            SQL;
    }

    public function parentCheckBeforeDeleteTrigger(string $table, string $parent_table, string $fk = 'parent_id', bool $drop = false): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // check if the table has that column
        if (! in_array($fk, core()->getColumns($table))) {
            return null;
        }
        $name = $table.'_parent_status_before_delete_check_trigger';
        if ($drop) {
            return "DROP TRIGGER IF EXISTS `$name`";
        }

        $parent = $this->makeRecordJsonObject($table, '@fk');

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` BEFORE UPDATE ON `$table` FOR EACH ROW
                BEGIN
                    SET @fk = OLD.$fk;
                    SET @parent = $parent
                    SET @parentStatus = JSON_UNQUOTE(JSON_EXTRACT(@parent, '$.record_status'));
                    SET @isImmutable = JSON_UNQUOTE(JSON_EXTRACT(@parent, '$.is_immutable'));
                    
                    IF @isImmutable = 1 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot delete from $table for a record whose parent ($fk) is immutable';
                    END IF;
                    IF @parentStatus = 'closed' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot delete from $table for a record whose parent ($fk) is closed';
                    ELSEIF @parentStatus = 'cancelled' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot delete from $table for a record whose parent ($fk) is cancelled';
                    ELSEIF @parentStatus = 'posted' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot delete from $table for a record whose parent ($fk) is posted';
                    END IF;
                END
            SQL;
    }

    public function parentCheckBeforeInsertTrigger(string $table, string $parent_table, string $fk_column = 'parent_id', bool $drop = false): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // check if the table has that column
        if (! in_array($fk_column, core()->getColumns($table))) {
            return null;
        }
        $name = $table.'_parent_status_before_insert_check_trigger';
        if ($drop) {
            return "DROP TRIGGER IF EXISTS `$name`";
        }

        $parent = $this->makeRecordJsonObject($parent_table, '@fk');

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` BEFORE INSERT ON `$table` FOR EACH ROW
                BEGIN
                    SET @fk = NEW.$fk_column;
                    SET @parent = $parent
                    SET @parentStatus = JSON_UNQUOTE(JSON_EXTRACT(@parent, '$.record_status'));
                    SET @isImmutable = JSON_UNQUOTE(JSON_EXTRACT(@parent, '$.is_immutable'));
                    
                    IF @isImmutable = 1 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot add records to $table whose parent ($fk_column) is immutable';
                    END IF;
                    IF @parentStatus = 'closed' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot add records to $table whose parent ($fk_column) is closed';
                    ELSEIF @parentStatus = 'cancelled' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot add records to $table whose parent ($fk_column) is cancelled';
                    ELSEIF @parentStatus = 'posted' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot add records to $table whose parent ($fk_column) is posted';
                    END IF;
                END
            SQL;
    }

    public function recordStatusTrigger(string $table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return false;
        }
        $name = $table.'_record_status_trigger';

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` BEFORE UPDATE ON `$table` FOR EACH ROW
                BEGIN
                    -- if the old record status is final, throw an error
                    IF OLD.record_status = 'closed' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot update a closed record';
                    END IF;

                    -- if the old record status is submitted, ensure the only allowed status is final or cancelled
                    IF OLD.record_status = 'posted' THEN
                        IF NEW.record_status NOT IN ('final', 'cancelled') THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'Cannot update a posted record to any status other than cancelled';
                        END IF;
                    END IF;

                    -- if the new record status is returned, reset the status to draft
                    IF NEW.record_status = 'returned' THEN
                        SET NEW.record_status = 'draft';
                    END IF;
                END
            SQL;
    }

    public function dropRecordStatusTrigger(string $table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return false;
        }
        $name = $table.'_record_status_trigger';

        return "DROP TRIGGER IF EXISTS `$name`";
    }

    public function codeTrigger($table, $padLength = 4, $column = 'code', $prefix = null): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // if the table does not have the code column, return
        if (! in_array($column, core()->getColumns($table))) {
            return null;
        }
        $prefix = $prefix ?? core()->generatePrefix($table);

        return
            <<<SQL
                DROP TRIGGER IF EXISTS `{$table}_code_trigger`;
                CREATE TRIGGER {$table}_code_trigger
                    BEFORE INSERT ON $table
                    FOR EACH ROW
                BEGIN
                    DECLARE id BIGINT;
                    SET id = (SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table');
                    IF NEW.$column IS NULL OR NEW.$column = '' THEN
                        SET NEW.$column = (SELECT CONCAT('$prefix', LPAD(id, $padLength, '0')));
                    END IF;
                END;
            SQL;
    }

    public function dropCodeTrigger($table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }

        return "DROP TRIGGER IF EXISTS  `{$table}_code_trigger`";
    }

    public function dropTriggerQuery(string $name): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return false;
        }

        return "DROP TRIGGER IF EXISTS  `$name`";
    }

    public function auditLogInsertTrigger(string $table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // skip for the audit_logs table
        if ($table === 'audit_logs') {
            return null;
        }
        // if the table doesn't have audit columns, return
        if (! array_intersect(core()->getColumns($table), ['creator_id', 'ip_address'])) {
            return null;
        }
        $name = $table.'_audit_log_insert_trigger';
        $new = $this->makeNewJsonObject($table);

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name` AFTER INSERT ON `$table` FOR EACH ROW
                BEGIN
                    SET @new = $new
                    SET @creator = JSON_UNQUOTE(JSON_EXTRACT(@new, '$.creator_id'));
                    IF @creator  = 'null' THEN
                        SET @creator = NULL;
                    END IF;
                    SET @ip = JSON_UNQUOTE(JSON_EXTRACT(@new, '$.ip_address'));
                    INSERT INTO audit_logs (user_id, action, table_name, record_id ,changed_values, old_values, new_values, ip_address, created_at, updated_at)
                    VALUES (@creator, 'create', '$table', NEW.id, NULL, NULL, @new, @ip, NOW(), NOW());
                END
            SQL;
    }

    public function dropAuditLogInsertTrigger($table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return false;
        }
        $name = $table.'_audit_log_insert_trigger';

        return "DROP TRIGGER IF EXISTS `$name`";
    }

    public function auditLogUpdateTrigger(string $table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // skip for the audit_logs table
        if ($table === 'audit_logs') {
            return null;
        }
        if (! array_intersect(core()->getColumns($table), ['creator_id', 'ip_address'])) {
            return null;
        }
        $name = $table.'_audit_log_update_trigger';
        $oldQuery = $this->makeOldJsonObject($table);
        $newQuery = $this->makeNewJsonObject($table);

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name`
                    BEFORE UPDATE ON `$table`
                    FOR EACH ROW
                BEGIN

                    -- Set Cursor for columns
                    DECLARE finished INTEGER DEFAULT 0;
                    DECLARE colName CHAR(64);
                    DECLARE oldVal JSON;
                    DECLARE newVal JSON;
                    DECLARE oldValues JSON DEFAULT JSON_OBJECT();
                    DECLARE newValues JSON DEFAULT JSON_OBJECT();
                    DECLARE changedValues JSON DEFAULT JSON_OBJECT();

                    -- Declare cursor for column names
                    DECLARE colCursor CURSOR FOR
                        SELECT COLUMN_NAME
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table';

                    -- Declare handler for end of loop
                    DECLARE CONTINUE HANDLER
                        FOR NOT FOUND SET finished = 1;

                    SET @old = $oldQuery
                    SET @new = $newQuery

                    -- Open the cursor
                    OPEN colCursor;

                    -- Loop through all columns
                    getCol: LOOP
                        FETCH colCursor INTO colName;
                        IF finished = 1 THEN
                            LEAVE getCol;
                        END IF;

                        -- Get the old and new values
                        SET oldVal = JSON_UNQUOTE(JSON_EXTRACT(@old, CONCAT('$.', colName)));
                        SET newVal = JSON_UNQUOTE(JSON_EXTRACT(@new, CONCAT('$.', colName)));

                        -- If the values are not equal, add the key and new value to the result
                        IF oldVal != newVal THEN
                            SET oldValues = JSON_INSERT(oldValues, CONCAT('$.', colName), oldVal);
                            SET newValues = JSON_INSERT(newValues, CONCAT('$.', colName), newVal);

                            SET changedValues = JSON_INSERT(changedValues, CONCAT('$.', colName), JSON_OBJECT('old', oldVal, 'new', newVal));
                        END IF;
                    END LOOP;

                    SET @updater = JSON_UNQUOTE(JSON_EXTRACT(@new, '$.updater_id'));
                    SET @ip = JSON_UNQUOTE(JSON_EXTRACT(@new, '$.ip_address'));

                    IF @updater  = 'null' THEN
                        SET @updater = NULL;
                    END IF;

                    -- Insert into audit_logs
                    INSERT INTO audit_logs (user_id, action, table_name, record_id, changed_values, old_values, new_values, ip_address, created_at, updated_at)
                    VALUES (@updater, 'update', '$table', NEW.id, changedValues, oldValues, newValues, @ip, NOW(), NOW());
                END;
            SQL;
    }

    public function dropAuditLogUpdateTrigger($table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        $name = $table.'_audit_log_update_trigger';

        return "DROP TRIGGER IF EXISTS `$name`";
    }

    public function auditLogDeleteTrigger(string $table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        // skip for the audit_logs table
        if ($table === 'audit_logs') {
            return null;
        }
        if (! array_intersect(core()->getColumns($table), ['creator_id', 'ip_address'])) {
            return null;
        }
        $name = $table.'_audit_log_delete_trigger';
        $old = $this->makeOldJsonObject($table);

        return <<<SQL
                DROP TRIGGER IF EXISTS `$name`;
                CREATE TRIGGER `$name`
                    BEFORE DELETE ON `$table`
                    FOR EACH ROW
                BEGIN
                    SET @old = $old
                    INSERT INTO audit_logs (action, table_name, record_id, changed_values, old_values, new_values, created_at, updated_at)
                    VALUES ('delete', '$table', OLD.id, NULL, @old, NULL, NOW(), NOW());
                END;
            SQL;
    }

    public function dropAuditLogDeleteTrigger(string $table): ?string
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'])) {
            return null;
        }
        $name = $table.'_audit_log_delete_trigger';

        return "DROP TRIGGER IF EXISTS `$name`";
    }

    private function makeOldJsonObject(string $table): string
    {
        $columns = core()->getColumns($table);
        $old = implode(', ', array_map(function ($column) {
            return "'$column', OLD.$column";
        }, $columns));

        return $oldQuery = "JSON_OBJECT($old);";
    }

    private function makeNewJsonObject(string $table): string
    {
        $columns = core()->getColumns($table);
        $new = implode(', ', array_map(function ($column) {
            return "'$column', NEW.$column";
        }, $columns));

        return $newQuery = "JSON_OBJECT($new);";
    }

    protected function makeRecordJsonObject(string $table, string $record_id): string
    {
        $columns = core()->getColumns($table);
        $args = implode(', ', array_map(function ($column) {
            return "'$column', $column";
        }, $columns));

        // select the record and convert it to a json object
        return "(SELECT JSON_OBJECT($args) FROM $table WHERE id = $record_id);";
    }
}
