---
name: database-auditor
description: Audit Laravel/MySQL database consistency, stock quantity, purchase-sale-storage mismatch, duplicate records, orphan records, soft deletes, and suspicious aggregate differences. Use for SQL debugging and data mismatch tasks.
---

# Database Auditor

When auditing database or stock mismatch issues:

1. First understand the related tables, columns, foreign keys, and soft delete rules.
2. Never assume one table is the source of truth without checking the business flow.
3. For stock/quantity issues, compare:
   - purchases
   - sales
   - storage
   - requests/circulation/adjustments if available
   - soft deleted rows
   - product type relationships

4. Use COALESCE for aggregate queries.
5. Group by product_id or productable_id carefully.
6. Clearly separate:
   - expected quantity
   - actual storage quantity
   - difference
   - possible reason

7. Avoid destructive SQL:
   - no DELETE
   - no UPDATE
   - no TRUNCATE
   unless explicitly requested.

8. Prefer read-only diagnostic queries first.

9. Final response must include:
   - diagnostic SQL
   - what each column means
   - likely causes
   - safe next steps
