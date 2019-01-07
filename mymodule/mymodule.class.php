<?php

class MyLink
{
    public static function gets($id_lang, $id_mymodule = null, $id_shop)
    {
        $sql = 'SELECT l.id_mymodule, l.new_window, s.name, ll.link, ll.label
				FROM '._DB_PREFIX_.'mymodule l
				LEFT JOIN '._DB_PREFIX_.'mymodule_lang ll ON (l.id_mymodule = ll.id_mymodule AND ll.id_lang = '.(int)$id_lang.' AND ll.id_shop='.(int)$id_shop.')
				LEFT JOIN '._DB_PREFIX_.'shop s ON l.id_shop = s.id_shop
				WHERE 1 '.((!is_null($id_mymodule)) ? ' AND l.id_mymodule = "'.(int)$id_mymodule.'"' : '').'
				AND l.id_shop IN (0, '.(int)$id_shop.')';

        return Db::getInstance()->executeS($sql);
    }

    public static function get($id_mymodule, $id_lang, $id_shop)
    {
        return self::gets($id_lang, $id_mymodule, $id_shop);
    }

    public static function getLinkLang($id_mymodule, $id_shop)
    {
        $ret = Db::getInstance()->executeS('
			SELECT l.id_mymodule, l.new_window, ll.link, ll.label, ll.id_lang
			FROM '._DB_PREFIX_.'mymodule l
			LEFT JOIN '._DB_PREFIX_.'mymodule_lang ll ON (l.id_mymodule = ll.id_mymodule AND ll.id_shop='.(int)$id_shop.')
			WHERE 1
			'.((!is_null($id_mymodule)) ? ' AND l.id_mymodule = "'.(int)$id_mymodule.'"' : '').'
			AND l.id_shop IN (0, '.(int)$id_shop.')
		');

        $link = array();
        $label = array();
        $new_window = false;

        foreach ($ret as $line) {
            $link[$line['id_lang']] = Tools::safeOutput($line['link']);
            $label[$line['id_lang']] = Tools::safeOutput($line['label']);
            $new_window = (bool)$line['new_window'];
        }

        return array('link' => $link, 'label' => $label, 'new_window' => $new_window);
    }

    public static function updateUrl($link)
    {
        for($i = 1; $i <= count($link); $i++) {
            if (substr($link[$i], 0, 7) !== "http://" && substr($link[$i], 0, 8) !== "https://") {
                $link[$i] = "http://" . $link[$i];
            }
        }
        return $link;
    }

    public static function add($link, $label, $newWindow = 0, $id_shop)
    {
        if (!is_array($label)) {
            return false;
        }
        if (!is_array($link)) {
            return false;
        }

        $link = self::updateUrl($link);
        Db::getInstance()->insert(
            'mymodule',
            array(
                'new_window'=>(int)$newWindow,
                'id_shop' => (int)$id_shop
            )
        );
        $id_mymodule = Db::getInstance()->Insert_ID();

        $result = true;

        foreach ($label as $id_lang=>$label) {
            $result &= Db::getInstance()->insert(
                'mymodule_lang',
                array(
                    'id_mymodule'=>(int)$id_mymodule,
                    'id_lang'=>(int)$id_lang,
                    'id_shop'=>(int)$id_shop,
                    'label'=>pSQL($label),
                    'link'=>pSQL($link[$id_lang])
                )
            );
        }

        return $result;
    }

    public static function update($link, $labels, $newWindow = 0, $id_shop, $id_link)
    {
        if (!is_array($labels)) {
            return false;
        }
        if (!is_array($link)) {
            return false;
        }

        $link = self::updateUrl($link);
        Db::getInstance()->update(
            'mymodule',
            array(
                'new_window'=>(int)$newWindow,
                'id_shop' => (int)$id_shop
            ),
            'id_mymodule = '.(int)$id_link
        );

        foreach ($labels as $id_lang => $label) {
            Db::getInstance()->update(
                'mymodule_lang',
                array(
                    'id_shop'=>(int)$id_shop,
                    'label'=>pSQL($label),
                    'link'=>pSQL($link[$id_lang])
                ),
                'id_mymodule = '.(int)$id_link.' AND id_lang = '.(int)$id_lang
            );
        }
    }

    public static function remove($id_mymodule, $id_shop)
    {
        $result = true;
        $result &= Db::getInstance()->delete('mymodule', 'id_mymodule = '.(int)$id_mymodule.' AND id_shop = '.(int)$id_shop);
        $result &= Db::getInstance()->delete('mymodule_lang', 'id_mymodule = '.(int)$id_mymodule);

        return $result;
    }
}