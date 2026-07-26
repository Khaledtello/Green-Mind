<?php

return [
    // Auth & Account Management
    'login_success' => 'Logged in successfully',
    'login_failed' => 'Invalid credentials',
    'logout_success' => 'Logged out successfully',
    'unauthenticated' => 'Unauthenticated, please login',
    'unauthorized' => 'Unauthorized',
    'password_updated' => 'Password updated successfully',
    'incorrect_password' => 'Current password is incorrect',
    'cannot_delete_self' => 'You cannot delete your own account while logged in',

    // Generic CRUD Messages
    'created' => 'Created successfully',
    'updated' => 'Updated successfully',
    'deleted' => 'Deleted successfully',

    // General Messages
    'not_found' => 'Resource not found',
    'success' => 'Operation completed successfully',
    'error' => 'An error occurred, please try again later',
    'ai_connection_failed' => 'Failed to connect to the AI diagnosis service',

    'crop_in_use' => 'Cannot delete crop because it is associated with plant batches',
    'diagnosis_error' => 'An error occurred while processing the image',

    'batch_locked' => 'Cannot edit a harvested batch. Please remove the harvest date first if you wish to make changes.',
    'harvested' => 'Batch harvested successfully. Future irrigation schedules have been stopped.',
    'undo_harvest' => 'Harvest undone successfully. Irrigation has been rescheduled.',
    'already_harvested' => 'This batch is already harvested',
    'not_harvested' => 'This batch is not harvested yet',

    'irrigation_completed' => 'Irrigation completed and next schedule calculated',
    'schedule_updated' => 'Irrigation schedule updated successfully',
    'undo_successful' => 'Last irrigation undone successfully',
    'no_irrigation_to_undo' => 'No completed irrigation to undo for this batch',
    'cannot_edit_past' => 'Cannot edit an already completed irrigation schedule',
    'already_irrigated' => 'This irrigation is already completed',
    'already_irrigated_today' => 'Cannot irrigate this batch more than once on the same day',
];
