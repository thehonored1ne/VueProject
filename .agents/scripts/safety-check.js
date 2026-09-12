#!/usr/bin/env node

import fs from 'node:fs';

const input = fs.readFileSync(0, 'utf-8');

let payload;
try {
    payload = JSON.parse(input);
} catch {
    console.log(JSON.stringify({ decision: 'allow' }));
    process.exit(0);
}

const commandLine = payload?.toolCall?.args?.CommandLine || '';

// Patterns considered destructive or risky
const dangerousPatterns = [
    /migrate:fresh/i,
    /migrate:reset/i,
    /db:wipe/i,
    /rm\s+-rf/i,
    /rmdir\s+\/s/i,
    /Remove-Item.*-Recurse.*-Force/i,
    /git\s+reset\s+--hard/i,
    /git\s+clean\s+-[a-zA-Z]*f/i,
    /drop\s+database/i,
    /drop\s+table/i,
];

const matchedPattern = dangerousPatterns.find((pattern) => pattern.test(commandLine));

if (matchedPattern) {
    console.log(
        JSON.stringify({
            decision: 'ask',
            reason: `Safety Guard: Potentially destructive command detected ("${commandLine}"). Please confirm before proceeding.`,
        }),
    );
} else {
    console.log(JSON.stringify({ decision: 'allow' }));
}
