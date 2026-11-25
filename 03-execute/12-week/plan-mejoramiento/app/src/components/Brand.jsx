// Visual identity shared with the public site (page/).

/** Logo: a court with its center line and center circle. */
export function BrandMark() {
  return (
    <svg className="brand-mark" viewBox="0 0 30 30" aria-hidden="true">
      <rect width="30" height="30" rx="7" fill="#1F5FAD" />
      <line x1="15" y1="3" x2="15" y2="27" stroke="#fff" strokeWidth="2" />
      <circle cx="15" cy="15" r="6" fill="none" stroke="#fff" strokeWidth="2" />
    </svg>
  );
}

/** Court markings used as a decorative background. */
export function CourtLines() {
  return (
    <svg className="court-lines" viewBox="0 0 1200 640" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
      <g fill="none" stroke="rgba(255,255,255,0.16)" strokeWidth="3">
        <rect x="40" y="40" width="1120" height="560" />
        <line x1="600" y1="40" x2="600" y2="600" />
        <circle cx="600" cy="320" r="90" />
        <path d="M40 180 h170 v280 h-170" />
        <path d="M1160 180 h-170 v280 h170" />
        <path d="M40 120 a 260 260 0 0 1 0 400" />
        <path d="M1160 120 a 260 260 0 0 0 0 400" />
      </g>
    </svg>
  );
}
