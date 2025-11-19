# Contribution Guide

Please follow the steps below to ensure a smooth contribution process.

## Table of Contents

- [Steps to Contribute](#steps-to-contribute)
  - [1. Create a Branch based on the task you are to work](#1-create-a-branch-based-on-the-task-you-are-to-work)
  - [2. Develop Your Feature](#2-develop-your-feature)
  - [3. Commit and Push](#3-commit-and-push)
  - [4. Pull Request Process](#4-pull-request-process)
- [Code Organization Rules](#code-organization-rules)
- [Resolving Merge Conflicts](#resolving-merge-conflicts)

## Steps to Contribute

### 1. Create a Branch based on the task you are to work

- Base your branch on `develop`:

  ```bash
  git checkout develop
  git pull origin develop
  git checkout -b your-branch-name
  ```

  - Suggested branch name:

  ```
  git checkout -b feature/<feature-name>
  ```

  - Use descriptive branch names, e.g.:
    - `feature/auction-list-page`
    - `feature/bid-form`
    - `bugfix/login-validation`

### 2. Develop Your Feature

- Make your changes and commit regularly.
- Keep your branch updated with the latest `develop` changes:

  ```bash
  git fetch origin
  git merge origin/develop
  ```

- Resolve any conflicts (see "Resolving Merge Conflicts" below).

### 3. Commit and Push

- Commit changes following the project's commit guidelines.
- Push your branch:
  ```bash
  git push origin <your-branch-name>
  ```

### 4. Pull Request Process

- Create a Pull Request (PR) to merge your branch into `develop` on GitHub.
- Use the PR template if provided, or include:
  - **Description of changes** (e.g., "Implements Auction Detail Page with bid form and real-time updates").
  - **Reference to the project specification** (e.g., "Addresses Auction Detail Page requirements").
  - **Screenshots for UI changes** (e.g., auction cards, hero section).

## Code Organization Rules

1. Components placement

- Global, reusable UI components go in `src/components` (e.g., `Navbar`, `Hero`, `Link`).
- Feature-specific components go in `src/features/<feature-name>/components`.

2. Pages and hooks usage

- Do not import hooks directly inside files under `src/pages`.
- Use required hooks within `src/features/<feature-name>/components`, then import those components into the corresponding page.

3. API and hooks location

- Place feature APIs and hooks under their feature folder: `src/features/<feature-name>/{api.js|hooks/}`.

Examples

```
src/
  components/           # global UI (Navbar, Hero, ...)
  features/
    auctions/
      api.js
      hooks/
      components/
        AuctionList.jsx
    auth/
      api.js
      hooks/
      components/
        LoginForm.jsx
  pages/
    Auctions.jsx        # imports AuctionList component, not hooks directly
```

## Resolving Merge Conflicts

Conflicts may occur if multiple developers modify the same lines in shared files. To resolve:

- Merge the latest `develop` into your branch:
  ```bash
  git fetch origin
  git merge origin/develop
  ```
- Resolve any conflicts manually, then commit the resolved files.

---

For further questions, please contact the project maintainers or open an issue.
