# kw_files_mapper

![Build Status](https://github.com/alex-kalanis/kw_files_mapper/actions/workflows/code_checks.yml/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/alex-kalanis/kw_files_mapper/v/stable.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_files_mapper)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/alex-kalanis/kw_files_mapper.svg?v1)](https://packagist.org/packages/alex-kalanis/kw_files_mapper)
[![License](https://poser.pugx.org/alex-kalanis/kw_files_mapper/license.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_files_mapper)
[![Code Coverage](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/badges/coverage.png?b=master&v=1)](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/?branch=master)

Manage access to storage with emulation of tree structure in database as source.

## PHP Installation

```bash
composer.phar require alex-kalanis/kw_files_mapper
```

(Refer to [Composer Documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction) if you are not
familiar with composer)


## PHP Usage

1.) Use your autoloader (if not already done via Composer autoloader)

2.) Add some external packages with connection to the local or remote services.

3.) Connect the correct processing libraries from "kalanis\kw_files_mapper\Processing" into your app. The correct one depends on your storage.

4.) Extend your libraries by interfaces inside the package "kw_files".

5.) Just call classes from package as instances of interfaces

6.) Let the system do its things
