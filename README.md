# race-condition-demonstrate
A Proof of Concept of a race-condition (also called race-hazard) with the virtual bank.

# Environments

Tested on:

- `Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.6 via XAMPP`
- `PHP/7.4.6`
- `python>=3.7`

# Usage

1. Host the server (virtual bank) on the localhost (or whatever).

2. Execute python script.

3. Check balances on the accounts.

# What is the race condition?

# Issues

The virtual bank is still incomplete regarding its security features (like sql-injection, xss, with many other attack vectors), please note that this is just for tests for the race-condition and I do not care about another security masures.  

If you have any issues or bugs, your contribution is always welcome.
