# kw_files_mapper

[![Build Status](https://app.travis-ci.com/alex-kalanis/kw_files_mapper.svg?branch=master)](https://app.travis-ci.com/github/alex-kalanis/kw_files_mapper)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/alex-kalanis/kw_files_mapper/v/stable.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_files_mapper)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/alex-kalanis/kw_files_mapper.svg?v1)](https://packagist.org/packages/alex-kalanis/kw_files_mapper)
[![License](https://poser.pugx.org/alex-kalanis/kw_files_mapper/license.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_files_mapper)
[![Code Coverage](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/badges/coverage.png?b=master&v=1)](https://scrutinizer-ci.com/g/alex-kalanis/kw_files_mapper/?branch=master)

Manage access to storage with emulation of tree structure in database as source.
Necessary for key-value storages. Also can behave the correct way when storing
in classical filesystems.

## PHP Installation

```
{
    "require": {
        "alex-kalanis/kw_files_mapper": "3.0"
    }
}
```

(Refer to [Composer Documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction) if you are not
familiar with composer)


## PHP Usage

1.) Use your autoloader (if not already done via Composer autoloader)

2.) Add some external packages with connection to the local or remote services.

3.) Connect the correct processing libraries from "kalanis\kw_files_mapper\Processing" into your app. The correct one depends on your storage.

4.) Extend your libraries by interfaces inside the package.

5.) Just call setting and render


### Changes

- v2 has difference in interface and class tree building for free name lookup


Notes to self: - all entries starts internally with the separators (usually slashes). It is not necessary
and sometimes counter-productive to add them when setting the starting dir. It behaves like a prefix.
