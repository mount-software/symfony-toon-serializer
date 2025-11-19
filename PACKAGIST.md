# Publishing to Packagist

This guide explains how to publish and maintain the `mountsoftware/symfony-toon-serializer` package on Packagist.

## Initial Setup

### 1. Register on Packagist

1. Go to [https://packagist.org](https://packagist.org)
2. Sign in with your GitHub/GitLab account
3. Click "Submit" in the top navigation
4. Enter your repository URL: `https://github.com/mount-software/symfony-toon-serializer`
   (or GitLab URL if hosted there)
5. Click "Check" and then "Submit"

### 2. Set Up Auto-Updates

Packagist needs to know when you push new versions. There are two methods:

#### Method A: GitLab Webhook (Recommended)

1. Go to your Packagist package page
2. Click on your username → "My Packages"
3. Find `mountsoftware/symfony-toon-serializer`
4. Click "Show API Token" and copy it
5. In GitLab, go to: **Settings → Webhooks**
6. Add a new webhook:
   - **URL**: `https://packagist.org/api/update-package?username=YOUR_USERNAME&apiToken=YOUR_TOKEN`
   - Replace `YOUR_USERNAME` and `YOUR_TOKEN` with your values
   - **Trigger**: Check "Tag push events" and "Push events"
   - **Enable SSL verification**: Yes
7. Click "Add webhook"
8. Test it by clicking "Test" → "Push events"

#### Method B: GitHub Webhook

If using GitHub:

1. Go to your Packagist package page and get your API token
2. In GitHub, go to: **Settings → Webhooks → Add webhook**
3. Configure:
   - **Payload URL**: `https://packagist.org/api/github?username=YOUR_USERNAME`
   - **Content type**: `application/json`
   - **Secret**: Your Packagist API token
   - **Events**: Just the push event
4. Click "Add webhook"

### 3. Connect GitHub/GitLab Service Integration (Alternative)

Packagist can also auto-update via service integration:

1. On Packagist package page, click the GitHub/GitLab button
2. Authorize Packagist to access your repositories
3. Select the repository
4. Packagist will automatically set up webhooks

## Creating Releases

### Semantic Versioning

Follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version (1.0.0 → 2.0.0): Breaking changes
- **MINOR** version (1.0.0 → 1.1.0): New features, backward compatible
- **PATCH** version (1.0.0 → 1.0.1): Bug fixes, backward compatible

### Release Process

1. **Update CHANGELOG** (if you have one):
   ```bash
   # Document changes in CHANGELOG.md
   git add CHANGELOG.md
   git commit -m "Update CHANGELOG for v1.0.0"
   ```

2. **Ensure tests pass**:
   ```bash
   composer test
   ```

3. **Create and push a tag**:
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```

4. **Packagist auto-updates**: Within minutes, Packagist will detect the new tag and update the package

5. **Verify on Packagist**: Check [your package page](https://packagist.org/packages/mountsoftware/symfony-toon-serializer)

### CI/CD Release Automation

The package includes automated release jobs for both GitLab CI and GitHub Actions:

#### GitLab CI (.gitlab-ci.yml)

```yaml
release:packagist:
  stage: release
  only:
    - tags
  script:
    - echo "Release $CI_COMMIT_TAG created"
```

#### GitHub Actions (.github/workflows/ci.yml)

```yaml
release:
  name: Create Release
  if: startsWith(github.ref, 'refs/tags/')
  steps:
    - uses: actions/create-release@v1
```

These jobs confirm the release in CI/CD and create GitHub releases. Packagist updates automatically via webhook regardless of which CI system you use.

## Version Constraints

Users can install specific versions:

```bash
# Latest stable version
composer require mountsoftware/symfony-toon-serializer

# Specific version
composer require mountsoftware/symfony-toon-serializer:^1.0

# Development version (not recommended for production)
composer require mountsoftware/symfony-toon-serializer:dev-main
```

## Badges

Add badges to your README.md:

```markdown
[![CI/CD](https://github.com/mountsoftware/symfony-toon-serializer/actions/workflows/ci.yml/badge.svg)](https://github.com/mountsoftware/symfony-toon-serializer/actions)
[![Latest Stable Version](https://poser.pugx.org/mountsoftware/symfony-toon-serializer/v/stable)](https://packagist.org/packages/mountsoftware/symfony-toon-serializer)
[![Total Downloads](https://poser.pugx.org/mountsoftware/symfony-toon-serializer/downloads)](https://packagist.org/packages/mountsoftware/symfony-toon-serializer)
[![License](https://poser.pugx.org/mountsoftware/symfony-toon-serializer/license)](https://packagist.org/packages/mountsoftware/symfony-toon-serializer)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)]()
```

## Maintenance

### Update Dependencies

Keep dependencies up to date:

```bash
composer update
composer test
git commit -am "Update dependencies"
git push
```

### Security Updates

Monitor security advisories:

```bash
composer audit
```

The GitLab CI includes a security check job that runs on every push.

## Troubleshooting

### Packagist Not Updating

1. **Check webhook**: Ensure webhook is configured correctly
2. **Manual update**: On your package page, click "Update"
3. **Check logs**: GitLab → Settings → Webhooks → Recent Deliveries
4. **Verify tag**: Ensure tag was pushed: `git ls-remote --tags origin`

### Version Not Showing

- Packagist only shows **tagged** versions
- Development branches appear as `dev-branch-name`
- Ensure tag follows format: `v1.0.0` or `1.0.0`

### Minimum Stability

If users can't install:

1. Check their `composer.json` has `"minimum-stability": "stable"`
2. Or they can use: `composer require mountsoftware/symfony-toon-serializer --prefer-stable`

## Best Practices

1. ✅ **Always tag releases**: Don't rely on branch names
2. ✅ **Follow SemVer**: Clear version increments
3. ✅ **Test before tagging**: Run full test suite
4. ✅ **Update docs**: Keep README current
5. ✅ **Maintain CHANGELOG**: Document all changes
6. ✅ **Security first**: Monitor and fix vulnerabilities
7. ✅ **Automate**: Use CI/CD for testing

## References

- [Packagist Documentation](https://packagist.org/about)
- [Composer Documentation](https://getcomposer.org/doc/)
- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)
