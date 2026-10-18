// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

/// @dev category already exists
error CategoryAlreadyExists();

/// @dev category does not exist
error CategoryDoesNotExist();

/// @dev Pagination end precedes start or requests more than 256 positions.
error InvalidRange();
