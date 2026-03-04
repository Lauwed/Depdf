# Contributing

Contributions are welcome and will be fully credited.

We accept contributions via pull requests on [GitHub](https://github.com/your-org/pdf-to-text-app).

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

- **[PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)** — The easiest way to apply the conventions is to install [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer):
  ```bash
  composer require --dev squizlabs/php_codesniffer
  ./vendor/bin/phpcs --standard=PSR12 src/ tests/
  ```

- **Add tests!** — Your patch won't be accepted if it doesn't have tests. Run the test suite with:
  ```bash
  composer install
  composer test
  ```

- **Document any change in behaviour** — Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** — We try to follow [SemVer v2.0.0](https://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** — If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** — Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](https://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#_squashing) before submitting.

## Getting Started

```bash
# Clone the repository
git clone https://github.com/your-org/pdf-to-text-app.git
cd pdf-to-text-app

# Install dependencies (PHP + Composer required)
composer install

# Install system dependency (Linux)
sudo apt install poppler-utils

# Run the web server
php -S localhost:8080 -t public

# Run tests
composer test
```

## Project Structure

```
src/          PHP classes (PSR-4, App\ namespace)
tests/        PHPUnit test suite (Tests\ namespace)
public/       Web entry point (index.php), JS and CSS assets
convert.php   CLI conversion tool
```

## Submitting a Pull Request

1. Fork the repository and create a feature branch from `main`.
2. Write your code following PSR-12.
3. Add or update tests to cover your change.
4. Update `README.md` if the behaviour or CLI interface changed.
5. Ensure the test suite passes: `composer test`.
6. Push to your fork and open a pull request.

**Happy contributing!**
