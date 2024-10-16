<?php

// SPDX-FileCopyrightText: 2009 Oliver Lillie <ollie@buggedcom.co.uk>
// SPDX-FileCopyrightText: 2006-2021 Tobias Leupold <tl at stonemx dot de>
//
// SPDX-License-Identifier: LGPL-3.0-or-later

namespace b8\storage;
use PDO;
/**
 * A MySQL storage backend
 *
 * @package b8
 */

class mysql extends storage_base
{

    private $mysql = null;
    private $table = null;

    protected function setup_backend(array $config)
    {
        if (! isset($config['resource'])
            || get_class($config['resource']) !== 'PDO') {
        
            throw new \Exception(mysql::class . ": No valid PDO object passed");
        }
        $this->mysql = $config['resource'];

        if (! isset($config['table'])) {
            throw new \Exception(mysql::class . ": No b8 wordlist table name passed");
        }
        $this->table = $config['table'];
    }

    protected function fetch_token_data(array $tokens)
    {

        $data = [];
        
        $k = 1;
        $sql = 'SELECT token, count_ham, count_spam FROM '.$this->table.' WHERE token IN (';
        foreach ($tokens as $token) {

            $sql .= ( $k == 1 ) ? '?' : ', ?';
            
            $k++;

        }
        $sql .= ')';

        $query = $this->mysql->prepare( $sql );
        $query->execute( $tokens );

        while ($row = $query->fetch()) {
            $data[$row[0]] = [ \b8\b8::KEY_COUNT_HAM  => $row[1],
                               \b8\b8::KEY_COUNT_SPAM => $row[2] ];
        }

        return $data;
    }

    protected function add_token(string $token, array $count)
    {
        $query = $this->mysql->prepare('INSERT IGNORE INTO ' . $this->table
                                       . '(token, count_ham, count_spam) VALUES(?, ?, ?)');
        $query->bindParam( 1, $token, PDO::PARAM_STR );
        $query->bindParam( 2, $count[\b8\b8::KEY_COUNT_HAM], PDO::PARAM_INT );
        $query->bindParam( 3, $count[\b8\b8::KEY_COUNT_SPAM], PDO::PARAM_INT );
        
        $query->execute();
    }

    protected function update_token(string $token, array $count)
    {
        $query = $this->mysql->prepare('UPDATE ' . $this->table
                                       . ' SET count_ham = ?, count_spam = ? WHERE token = ?');
                                  
        $query->bindParam( 1, $count[\b8\b8::KEY_COUNT_HAM], PDO::PARAM_INT );
        $query->bindParam( 2, $count[\b8\b8::KEY_COUNT_SPAM], PDO::PARAM_INT );
        $query->bindParam( 3, $token, PDO::PARAM_STR );

        $query->execute();
    }

    protected function delete_token(string $token)
    {
        $query = $this->mysql->prepare('DELETE FROM ' . $this->table . ' WHERE token = ?');
        $query->bindParam( 1, $token, PDO::PARAM_STR );
        $query->execute();
    }

    protected function start_transaction()
    {
        $this->mysql->beginTransaction();
    }

    protected function finish_transaction()
    {
        $this->mysql->commit();
    }

}
