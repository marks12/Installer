# Installer
Модуль для установки и первоначальной конфигурации жизненно необходимых компонентов системы TsvZar

## Features
Создайте файл data/installer.data в вашем модуле для выполнения следующих операций:

* copy. Используйте формат: 

copy:source_from_module_dir:destonation_from_home_dir:(bool)create folder if need:(bool)rewrite files dest if exists

### Пример:
copy:data/tsv_searchpagination.phtml:module/Application/partials/tsv_searchpagination.phtml:1:1

### Комменты
Комментарии в файле data/installer.data необходимо начинать с новой строки с одного из символов (#;/=)

## Installation

composer require marks12/installer:*

## Start configuration

zf configure
