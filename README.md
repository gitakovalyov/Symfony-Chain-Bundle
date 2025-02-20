# Symfony-Chain-Bundle
Symfony bundle that implements command chaining functionality

---

##  Install on a Local Environment

1. Navigate to the `docker` directory:
   ```sh
   cd docker
   ```

2. Build and start the containers:
   ```sh
   docker-compose up -d --build
   ```

3. Access the PHP container:
   ```sh
   docker-compose exec php bash
   ```

4. Install dependencies:
   ```sh
   composer install
   ```

---

##  Test Commands

Run the following commands to test the application:

- **Execute the `foo:hello` command**:
  ```sh
  php bin/console foo:hello
  ```

- **Execute the `bar:hi` command**:
  ```sh
  php bin/console bar:hi
  ```

---

##  Run Tests

To execute unit and functional tests, run:
```sh
vendor/bin/phpunit bundles/
```

---
