import * as Sentry from '@sentry/nextjs';
import type { NextApiRequest, NextApiResponse } from 'next'

// FIXME: https://github.com/getsentry/sentry-javascript/issues/3852
// API resolved without sending a response for *, this may result in stalled requests.
export default (fn: (req: NextApiRequest, res: NextApiResponse<any>) => any) => {
  return async (req: NextApiRequest, res: NextApiResponse<any>) => {
    await Sentry.withSentry(fn)(req, res);
    await res.end();
  }
};