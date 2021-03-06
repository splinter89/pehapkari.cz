---
id: 85
title: "How to Speedup Coverage on Travis by 95 %"
perex: |
    It was late in the night. He was looking at CI builds to make sure everything is ready for a morning presentation.
    <br>
    <br>
    **The build took over 45 minutes**. What was wrong? He was scared, took a deep breath, and looked at Travis build detail anyway.
    <br>
    <br>
    "What? Code coverage? All the stress for this?"
    <br>
    **"We should remove it,"** he thought, "CI should give fast feedback... or is there another way?"

author: 1
tweet: "New Post on #pehapkari Blog: How to Speedup Coverage on Travis by 95 %"
---

Do you find this story resembling your daily job? We had the same problem. We tolerated for 2 years, but in 2020 we looked for a better way.

<img src="/assets/images/posts/coverage_slow.png">

## Status Quo: Xdebug

The most common way in the open-source nowadays is Xdebug with Coveralls. [Coveralls.io](http://coveralls.io/) is an open-source, free service, that consumes your PHPUnit coverage data, and turns them into one significant number.

That's how can have sexy coverage badge in your repository:

<img src="https://img.shields.io/coveralls/Symplify/Symplify/master.svg?style=flat-square">

How do we make it happen on Travis?

```yaml
script:
    - vendor/bin/phpunit --coverage-clover coverage.xml
```

In the job context:

```yaml
# .travis.yml
jobs:
    include:
        -
            stage: coverage
            php: 7.3
            name: Test Coverage
            script:
                - vendor/bin/phpunit --coverage-clover coverage.xml
                # Coveralls.io
                - wget https://github.com/php-coveralls/php-coveralls/releases/download/v2.1.0/php-coveralls.phar
                - php php-coveralls.phar --verbose
```

## 2. Faster with phpdbg

I've learned about phpdbg from this [short and clear post by KIZU 514](https://kizu514.com/blog/phpdbg-is-much-faster-than-xdebug-for-code-coverage/).

One-line, no-install setup:

```yaml
script:
    - phpdbg -qrr -d memory_limit=-1 vendor/bin/phpunit --coverage-clover coverage.xml
```

In full job:

```yaml
# .travis.yml
jobs:
    include:
        -
            stage: coverage
            php: 7.3
            name: Test Coverage
            script:
                - phpdbg -qrr -d memory_limit=-1 vendor/bin/phpunit --coverage-clover coverage.xml
                # Coveralls.io
                - wget https://github.com/php-coveralls/php-coveralls/releases/download/v2.1.0/php-coveralls.phar
                - php php-coveralls.phar --verbose
```

**Mind the `-d memory_limit=-1`.** The memory was exhausted very soon. We would care if this was a production code. But CI is build, run and throw away container, so allowing unlimited memory is ok.


## 3. Even Faster with PCOV

It's better to have PHPUnit 8+, but what if [don't have it yet](/blog/2019/11/04/still-on-phpunit-4-come-to-phpunit-8-together-in-a-day/)? You can [read about PCOV here](https://kizu514.com/blog/pcov-is-better-than-phpdbg-and-xdebug-for-code-coverage/), we'll get right to the business.

2-lines run with setup:

```yaml
script:
    - pecl install pcov
    - vendor/bin/phpunit --coverage-clover coverage.xml
```

In jobs context:

```yaml
# .travis.yml
jobs:
    include:
        -
            stage: coverage
            php: 7.3
            name: Test Coverage
            script:
                - pecl install pcov
                - vendor/bin/phpunit --coverage-clover coverage.xml
                # Coveralls.io
                - wget https://github.com/php-coveralls/php-coveralls/releases/download/v2.1.0/php-coveralls.phar
                - php php-coveralls.phar --verbose
```

**PCOV took only 1,5 minutes**, that's great!

The coverage number changed from 77 % to 73 %, though. It can be seriously misleading. If that would be ~0,5 % than
with Xdebug, it's ok. Imagine someone tries to cover that is already covered? **4 % is too much**.


## Final Results

- Xdebug - 37 minutes, 77,5 % code coverage
- phpdbg - 3 minutes, 77,1 % code coverage
- pcov - 1,5 minutes, 73 % code coverage

...and the winner is:

<img src="/assets/images/posts/coverage_fast.png">

<br>

**phpdbg** 🎉

It was fast enough (other parallel jobs took around 2 minutes on average), and also provided code coverage similar to mainstream Xdebug value, just 0,4 % different.

<br>

But that was *our specific* codebase. Be sure to try option 2. and 3. on your code, in one PR, to see **what suits you**.

## The Future?

Derrick, the Xdebug author, [wrote about Xdebug 2.9](https://derickrethans.nl/crafty-code-coverage.html) that should speed up 22 mins build into **1.2 min**.

It [might take some time to get it on Travis](https://travis-ci.community/t/new-faster-xdebug-2-9-is-out/6372), which has nov Xdebug 2.7.

We'll see :)


## Travis Tip: Remove Xdebug by Default

Xdebug makes everything very, very slow.

- PHPUnit tests run **without** Xdebug: 20 **secs**
- the same **with** Xdebug: 35 **minutes**

<br>

<div class="text-center">
<h3>Rule of the thumb</h3>
</div>

<blockquote class="blockquote text-center">
    Enable Xdebug explicitly, if you need it.
    <br>
    Keep it disabled by default.
</blockquote>

You can't imagine how many false performance positives we tried to solve, because we didn't have this rule.

<br>

**To remove it**, just update `.travis.yml` with:

```yaml
# .travis.yml
before_install:
    # turn off XDebug
    - phpenv config-rm xdebug.ini
```

Voilá!

<br>

Happy fast testing!
