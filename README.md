# Signaling

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-BSD-blue.svg)](LICENSE)

A PHP library for managing signaling mechanisms in WebRTC. This package supports signaling over WebSocket, TCP, UDP, and UNIX sockets using a unified adapter-based architecture with ReactPHP.

##  Features

- Unified signaling interface with adapter pattern
- WebSocket, TCP, UDP, and UNIX socket support
- Works with ReactPHP event loop
- Send and receive SDP offers/answers and ICE candidates


## Requirements

- PHP ≥ 8.4 with [protobuf](https://pecl.php.net/package/protobuf) and FFI extension enabled
- install protobuf (for more information visit https://github.com/protocolbuffers/protobuf/tree/main/php)

## Documentation

This package is part of the PHP WebRTC library. For complete documentation, examples, and API reference, visit:

[PHP WebRTC Documentation](https://www.quasarstream.com/php-webrtc)

## Credits

### Authors

- **Amin Yazdanpanah**  
  - Website: [aminyazdanpanah.com](https://www.aminyazdanpanah.com)
  - Email: [github@aminyazdanpanah.com](mailto:github@aminyazdanpanah.com)

- **Sana Moniri**  
  - GtiHub: [sanamoniri](https://github.com/sanamoniri)

## Reporting Issues

Found a bug? Please report it on our [issues](https://github.com/php-webrtc/signaling/issues).

## License

BSD 3-Clause License. See [LICENSE](LICENSE) for details.
