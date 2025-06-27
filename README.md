# Remote Grabber PHP

**RemoteGrabberPHP** is a lightweight, single-file PHP utility that allows users to remotely download files (especially `.zip`, `.rar`, etc.) by simply entering a URL. It provides file type and size info before downloading, supports resumable downloads, and is built to bypass typical PHP and web server limitations.

## 🔧 Features

- ✅ URL input form to fetch remote files
- ✅ Automatic content-type and file size detection
- ✅ Confirmation step before download
- ✅ Custom filename support
- ✅ Resumable download (if server supports `Accept-Ranges`)
- ✅ Large file handling with no PHP execution limits
- ✅ Logging of all activity (`grabber.log`)
- ✅ Works on cPanel, shared hosting, or VPS
- ✅ One single `index.php` file

## 📦 Installation

1. Clone or download the repo:

```bash
git clone https://github.com/BaseMax/RemoteGrabberPHP.git
````

2. Upload to your server (`public_html/RemoteGrabberPHP/` or any subfolder)

3. Ensure the following files exist in the directory:

```
index.php          ← Main script
php.ini            ← Extends execution time
.htaccess          ← Backup for Apache-based hosts
grabber.log        ← Automatically created on first use
```

> 💡 If you're on shared hosting (like cPanel), the provided `php.ini` and `.htaccess` help break default timeouts.

## 🚀 Usage

1. Visit `https://yourdomain.com/RemoteGrabberPHP/`
2. Enter a remote file URL (e.g., a `.zip` or `.tar.gz`)
3. Optionally, provide a custom filename
4. Click **Check & Continue**
5. Review the file info
6. Confirm and start download

## 🧰 Configuration Files

### `.htaccess`

Sets PHP values for Apache environments.

### `php.ini`

Overrides default execution and memory limits.

```ini
max_execution_time = 0
memory_limit = 512M
upload_max_filesize = 2048M
post_max_size = 2048M
zlib.output_compression = Off
output_buffering = Off
```

> You can edit these files depending on your host's capabilities.

## 📄 Logging

All activities and errors are logged in `grabber.log` (created in the same folder). This is useful for debugging or usage auditing.

## ⚠️ Security Note

This script does **not** perform strict URL validation or authentication. It is recommended to:

* Place the tool in a non-public folder
* Protect access with `.htpasswd` or similar
* Avoid exposing it in production environments without access control

## 📜 License

This project is licensed under the MIT License.
See [LICENSE](LICENSE) for more details.

## ✨ Author

Created by [Max Base](https://github.com/BaseMax)

---

Made with ❤️ to simplify remote file fetching.
